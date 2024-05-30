<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Controller;

use Exception;
use JsonException;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidSolutionCatalysts\Unzer\Model\Order as UnzerOrder;
use OxidSolutionCatalysts\Unzer\Model\TmpOrder;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidSolutionCatalysts\Unzer\Service\Transaction;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use OxidSolutionCatalysts\Unzer\Service\UnzerWebhooks;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use UnzerSDK\Constants\PaymentState;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\Payment;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DispatcherController extends FrontendController
{
    use ServiceContainer;

    /**
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws UnzerApiException
     * @throws Exception
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    public function updatePaymentTransStatus(): void
    {
        $result = '';

        /** @var string $jsonRequest */
        $jsonRequest = file_get_contents('php://input');

        $aJson = [];

        try {
            /** @var array $aJson */
            $aJson = json_decode($jsonRequest, true, 512, JSON_THROW_ON_ERROR);
            if (!count($aJson)) {
                throw new JsonException('Invalid Json');
            }
        } catch (JsonException $e) {
            Registry::getUtils()->showMessageAndExit("Invalid Json");
        }

        /** @var array $url */
        $url = parse_url($aJson['retrieveUrl']);
        /** @var Transaction $transaction */
        $transaction = $this->getServiceFromContainer(Transaction::class);
        $aPath = explode("/", $url['path']);
        $typeid = end($aPath);

        /** @var Request $request */
        $request = Registry::getRequest();
        /** @var string $context */
        $context = $request->getRequestParameter('context', 'shop');
        $unzerWebhooks = $this->getServiceFromContainer(UnzerWebhooks::class);
        $unzerKey = $unzerWebhooks->getUnzerKeyFromWebhookContext($context);
        if (empty($unzerKey)) {
            Registry::getUtils()->showMessageAndExit("Invalid Webhook call");
        }

        if (
            $url['scheme'] !== "https" ||
            (
                $url['host'] !== "api.unzer.com" &&
                $url['host'] !== "sbx-api.heidelpay.com"
            )
        ) {
            Registry::getUtils()->showMessageAndExit("No valid retrieveUrl");
        }

        $unzer = $this->getServiceFromContainer(UnzerSDKLoader::class)->getUnzerSDKbyKey($unzerKey);
        $resource = $unzer->fetchResourceFromEvent($jsonRequest);
        $paymentId = $resource->getId();

        if (!$transaction->isValidTransactionTypeId($typeid)) {
            Registry::getUtils()->showMessageAndExit("Invalid type id");
        }

        if (is_string($paymentId)) {
            /** @var UnzerOrder $order */
            $order = oxNew(Order::class);
            /** @var array $data */
            $data = $transaction::getTransactionDataByPaymentId($paymentId);
            $unzerPayment = $unzer->fetchPayment($paymentId);
            if ($order->load($data[0]['OXORDERID'])) {
                $result = $this->writeOrder($unzerPayment, $order, $paymentId);
            } else {
                $this->prepareTmpOrder($unzerPayment);
            }
        }

        Registry::getUtils()->showMessageAndExit($result);
    }

    private function handleTmpOrder(
        Payment $unzerPayment,
        TmpOrder $tmpOrder,
        array $tmpData,
        bool $error
    ): void {
        $translator = $this->getServiceFromContainer(Translator::class);
        $result = $translator->translate('oscunzer_ERROR_HANDLE_TMP_ORDER');
        if ($tmpOrder->load($tmpData['OXID'])) {
            $aOrderData = unserialize(base64_decode($tmpData['TMPORDER']), ['allowed_classes' => [Order::class]]);
            /** @var UnzerOrder $oOrder */
            $oOrder = is_array($aOrderData) && isset($aOrderData['order']) ? $aOrderData['order'] : null;
            if ($oOrder) {
                $oOrder->finalizeTmpOrder($unzerPayment, $error);
                $tmpOrder->assign(['status' => 'FINISHED']);
                $tmpOrder->save();
                $result = $translator->translate('oscunzer_SUCCESS_HANDLE_TMP_ORDER');
            }
        }
        Registry::getUtils()->showMessageAndExit($result);
    }

    private function prepareTmpOrder(Payment $unzerPayment): void
    {
        $tmpOrder = oxNew(TmpOrder::class);
        $orderId = $unzerPayment->getBasket() ? $unzerPayment->getBasket()->getOrderId() : '';
        $tmpData = $tmpOrder->getTmpOrderByUnzerId($orderId);
        if (
            isset($tmpData['OXID']) &&
            $tmpOrder->load($tmpData['OXID']) &&
            $this->hasExceededTimeLimit($tmpOrder)
        ) {
            $bError = !($unzerPayment->getState() === PaymentState::STATE_COMPLETED ||
                $unzerPayment->getState() === PaymentState::STATE_CANCELED ||
                $unzerPayment->getState() === PaymentState::STATE_PENDING);
            $this->handleTmpOrder($unzerPayment, $tmpOrder, $tmpData, $bError);
        }
    }

    /**
     * @param $tmpOrder TmpOrder
     * @return bool
     */
    private function hasExceededTimeLimit(TmpOrder $tmpOrder): bool
    {
        $defTimeDiffMin = Registry::getConfig()->getConfigParam('defTimeDiffMin', 5);
        $timeDiffSec = $defTimeDiffMin * 60;
        $tmpOrderTime = is_string($tmpOrder->getFieldData('timestamp'))
            ? $tmpOrder->getFieldData('timestamp')
            : '';
        $tmpOrderTimeUnix = strtotime($tmpOrderTime);
        $nowTimeUnix = time();
        $difference = $nowTimeUnix - $tmpOrderTimeUnix;

        return $difference >= $timeDiffSec;
    }

    private function writeOrder(Payment $unzerPayment, UnzerOrder $order, string $paymentId): string
    {
        if ($unzerPayment->getState() === PaymentState::STATE_COMPLETED) {
            $order->markUnzerOrderAsPaid();
        }

        if ($unzerPayment->getState() === PaymentState::STATE_CANCELED) {
            $order->cancelOrder();
        }

        $translator = $this->getServiceFromContainer(Translator::class);
        $transactionService = $this->getServiceFromContainer(Transaction::class);
        $result = $translator->translate('oscunzer_TRANSACTION_NOTHINGTODO') . $paymentId;

        if (
            $transactionService->writeTransactionToDB(
                $order->getId(),
                $order->getOrderUser()->getId() ?: '',
                $unzerPayment
            )
        ) {
            $result = sprintf(
                $translator->translate('oscunzer_TRANSACTION_CHANGE'),
                $unzerPayment->getStateName(),
                $paymentId
            );
        }

        return $result;
    }
}
