<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Controller;

use JsonException;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Model\Order;
use OxidSolutionCatalysts\Unzer\Traits\Request;
use OxidSolutionCatalysts\Unzer\Model\TmpOrder;
use OxidSolutionCatalysts\Unzer\Service\Transaction;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use OxidSolutionCatalysts\Unzer\Service\UnzerWebhooks;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use UnzerSDK\Constants\PaymentState;
use UnzerSDK\Resources\Payment;

class DispatcherController extends FrontendController
{
    use ServiceContainer;
    use Request;

    /** @var \OxidSolutionCatalysts\Unzer\Service\Transaction $transaction */
    private $transaction;

    /** @var \OxidSolutionCatalysts\Unzer\Service\UnzerWebhooks $unzerWebhooks */
    private $unzerWebhooks;

    /** @var \OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader $unzerSDKLoader */
    private $unzerSDKLoader;

    /** @var \OxidSolutionCatalysts\Unzer\Service\Translator $translator */
    private $translator;

    public function __construct()
    {
        parent::__construct();
        $this->transaction = $this->getServiceFromContainer(Transaction::class);
        $this->unzerWebhooks = $this->getServiceFromContainer(UnzerWebhooks::class);
        $this->unzerSDKLoader = $this->getServiceFromContainer(UnzerSDKLoader::class);
        $this->translator = $this->getServiceFromContainer(Translator::class);
    }

    public function updatePaymentTransStatus(): void
    {
        $jsonRequest = file_get_contents('php://input');
        if ($jsonRequest === false) {
            $this->exitWithMessage("Invalid Json");
            return;
        }

        $aJson = $this->decodeJson((string)$jsonRequest);
        if (!is_array($aJson) || !isset($aJson['retrieveUrl'])) {
            $this->exitWithMessage("Invalid Json");
            return;
        }

        $url = null;
        if (isset($aJson['retrieveUrl'])) {
            $url = parse_url($aJson['retrieveUrl']);
        }

        if (!is_array($url)) {
            $this->exitWithMessage("Invalid URL");
            return;
        }

        $typeid = $this->getTypeId($url);
        if ($this->isInvalidRequest($url, $typeid)) {
            $this->exitWithMessage("Invalid Webhook call");
            return;
        }

        $unzerKey = $this->getUnzerKeyFromContext();
        if (empty($unzerKey)) {
            $this->exitWithMessage("Invalid Webhook context");
            return;
        }

        $unzer = $this->unzerSDKLoader->getUnzerSDKbyKey($unzerKey);
        $resource = $unzer->fetchResourceFromEvent((string)$jsonRequest);
        $paymentId = $resource->getId();

        if ($paymentId) {
            $result = $this->processPayment($unzer, $paymentId);
            Registry::getUtils()->showMessageAndExit($result);
        }
    }

    private function exitWithMessage(string $message): void
    {
        Registry::getUtils()->showMessageAndExit($message);
    }

    private function getUnzerKeyFromContext(): string
    {
        $context = $this->getContext();
        return $this->unzerWebhooks->getUnzerKeyFromWebhookContext($context);
    }

    private function processPayment(\UnzerSDK\Unzer $unzer, string $paymentId): string
    {
        $order = oxNew(Order::class);
        $data = $this->transaction::getTransactionDataByPaymentId($paymentId);

        if (!is_array($data) || !isset($data[0]['OXORDERID'])) {
            return "Invalid Order Data";
        }

        $unzerPayment = $unzer->fetchPayment($paymentId);
        if ($order->load($data[0]['OXORDERID'])) {
            return $this->updateOrder($order, $unzerPayment, $paymentId);
        }

        return $this->handleTmpOrder($unzerPayment);
    }

    private function decodeJson(string $jsonRequest): ?array
    {
        try {
            $aJson = json_decode($jsonRequest, true, 512, JSON_THROW_ON_ERROR);
            return is_array($aJson) ? $aJson : null;
        } catch (JsonException $e) {
            return null;
        }
    }

    private function getTypeId(array $url): string
    {
        $pathSegments = explode("/", $url['path']);
        return end($pathSegments);
    }

    private function isInvalidRequest(array $url, string $typeid): bool
    {
        return $url['scheme'] !== "https" ||
            ($url['host'] !== "api.unzer.com" && $url['host'] !== "sbx-api.heidelpay.com") ||
            !$this->transaction->isValidTransactionTypeId($typeid);
    }

    private function getContext(): string
    {
        return $this->getUnzerStringRequestParameter('context', 'shop');
    }

    private function updateOrder(Order $order, Payment $unzerPayment, string $paymentId): string
    {
        switch ($unzerPayment->getState()) {
            case PaymentState::STATE_COMPLETED:
                $this->markUnzerOrderAsPaid($order);
                break;
            case PaymentState::STATE_CANCELED:
                $this->cancelOrder($order);
                break;
        }

        if (
            $this->transaction->writeTransactionToDB(
                $order->getId(),
                $order->getOrderUser()->getId() ?: '',
                $unzerPayment
            )
        ) {
            return sprintf(
                $this->translator->translate('oscunzer_TRANSACTION_CHANGE'),
                $unzerPayment->getStateName(),
                $paymentId
            );
        }

        return $this->translator->translate('oscunzer_TRANSACTION_NOTHINGTODO') . $paymentId;
    }

    private function handleTmpOrder(Payment $unzerPayment): string
    {
        $tmpOrder = oxNew(TmpOrder::class);
        $orderId = $unzerPayment->getBasket() ? $unzerPayment->getBasket()->getOrderId() : '';
        $tmpData = $tmpOrder->getTmpOrderByUnzerId($orderId);

        if (
            isset($tmpData['OXID']) &&
            $tmpOrder->load($tmpData['OXID']) &&
            $this->hasExceededTimeLimit($tmpOrder)
        ) {
            $bError = !(
                $unzerPayment->getState() === PaymentState::STATE_COMPLETED ||
                $unzerPayment->getState() === PaymentState::STATE_CANCELED ||
                $unzerPayment->getState() === PaymentState::STATE_PENDING
            );

            return $this->finalizeTmpOrder($unzerPayment, $tmpOrder, $tmpData, $bError);
        }

        return $this->translator->translate('oscunzer_ERROR_HANDLE_TMP_ORDER');
    }

    private function finalizeTmpOrder(
        Payment $unzerPayment,
        TmpOrder $tmpOrder,
        array $tmpData,
        bool $bError
    ): string {
        if ($tmpOrder->load($tmpData['OXID'])) {
            $aOrderData = unserialize(base64_decode($tmpData['TMPORDER']), ['allowed_classes' => [Order::class]]);
            $oOrder = is_array($aOrderData) && isset($aOrderData['order']) ? $aOrderData['order'] : null;

            if ($oOrder) {
                $oOrder->finalizeTmpOrder($unzerPayment, $bError);
                $tmpOrder->assign(['status' => 'FINISHED']);
                $tmpOrder->save();
                return $this->translator->translate('oscunzer_SUCCESS_HANDLE_TMP_ORDER');
            }
        }

        return $this->translator->translate('oscunzer_ERROR_HANDLE_TMP_ORDER');
    }

    private function hasExceededTimeLimit(TmpOrder $tmpOrder): bool
    {
        $defTimeDiffMin = Registry::getConfig()->getConfigParam('defTimeDiffMin', 5);
        $timeDiffSec = $defTimeDiffMin * 60;
        $tmpOrderTime = is_string($tmpOrder->getFieldData('timestamp')) ? $tmpOrder->getFieldData('timestamp') : '';
        $tmpOrderTimeUnix = strtotime($tmpOrderTime);
        $nowTimeUnix = time();
        $difference = $nowTimeUnix - $tmpOrderTimeUnix;

        return $difference >= $timeDiffSec;
    }

    private function markUnzerOrderAsPaid(Order $order): void
    {
        $order->markUnzerOrderAsPaid();
        $order->assign(['oxtransstatus' => 'OK']);
        $order->save();
    }

    private function cancelOrder(Order $order): void
    {
        $order->assign(['oxtransstatus' => 'NOT_FINISHED']);
        $order->save();
    }
}
