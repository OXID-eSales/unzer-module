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
use OxidEsales\Eshop\Core\Counter;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidSolutionCatalysts\Unzer\Model\TmpOrder;
use OxidSolutionCatalysts\Unzer\Service\Transaction;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use OxidSolutionCatalysts\Unzer\Service\UnzerWebhooks;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use UnzerSDK\Constants\PaymentState;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\Payment;

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
         //   Registry::getUtils()->showMessageAndExit("Invalid type id");
        }

        if (is_string($paymentId)) {
            /** @var \OxidSolutionCatalysts\Unzer\Model\Order $order */
            $order = oxNew(Order::class);
            /** @var array $data */
            $data = $transaction::getTransactionDataByPaymentId($paymentId);

            $unzerPayment = $unzer->fetchPayment($paymentId);

            if ($order->load($data[0]['OXORDERID'])) {
                if ($unzerPayment->getState() === PaymentState::STATE_COMPLETED) {
                    $order->markUnzerOrderAsPaid();
                }

                if ($unzerPayment->getState() === PaymentState::STATE_CANCELED) {
                    $order->cancelOrder();
                }

                $translator = $this->getServiceFromContainer(Translator::class);
                $transactionService = $this->getServiceFromContainer(Transaction::class);

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
                } else {
                    $result = $translator->translate('oscunzer_TRANSACTION_NOTHINGTODO') . $paymentId;
                }
            } else {
                /** @var TmpOrder $tmpOrder */
                $tmpOrder = oxNew(TmpOrder::class);
                sleep(10);
                $tmpData = $tmpOrder->getTmpOrderByUnzerId($unzerPayment->getBasket()->getOrderId());
                if (
                    $unzerPayment->getState() === PaymentState::STATE_COMPLETED ||
                    $unzerPayment->getState() === PaymentState::STATE_CANCELED ||
                    $unzerPayment->getState() === PaymentState::STATE_PENDING
                ) {
                    if ($tmpOrder->load($tmpData['OXID'])) {
                        $this->cleanUpOrder($unzerPayment, $tmpOrder, $tmpData);
                    }
                }
            }
        }

        Registry::getUtils()->showMessageAndExit($result);
    }

    /**
     * @param $unzerPayment Payment
     * @return void
     * @throws Exception
     */
    protected function cleanUpOrder($unzerPayment, $tmpOrder, $tmpData)
    {
        if ($tmpOrder->load($tmpData['OXID'])) {
            $aOrderData = unserialize(base64_decode($tmpData['TMPORDER']));
            $this->setOrderStatus($aOrderData['order'], $unzerPayment);
            $tmpOrder->assign(['STATUS' => 'FINISHED']);
            $tmpOrder->save();
            /** @TODO here we could send an email to the user (depending on the state) */
            Registry::getUtils()->setHeader('HTTP/1.1 200 OK');
            Registry::getUtils()->showMessageAndExit('200');
        }
    }

    /**
     * @param $oOrder Order
     * @param $unzerPayment Payment
     * @return void
     * @throws Exception
     */
    protected function setOrderStatus($oOrder, $unzerPayment)
    {
        $orderStatus = $oOrder->oxorder__oxtransstatus->rawValue;
        if ($orderStatus === 'NOT_FINISHED') {
            $oOrder->oxorder__oxtransstatus = new Field('OK', Field::T_RAW);
            $oOrder->oxorder__oxfolder      = new Field('ORDERFOLDER_NEW', Field::T_RAW);
            $oOrder->oxorder__oxpaid        = new Field(date('Y-m-d H:i:s'));
            switch ($unzerPayment->getState()) {
                case PaymentState::STATE_COMPLETED:
                    $oOrder->markUnzerOrderAsPaid();
                    break;
                case PaymentState::STATE_CANCELED:
                    $oOrder->cancelOrder();
                    break;
            }
            $oOrder->initWriteTransactionToDB($unzerPayment);
            if (!$oOrder->getFieldData('oxordernr')) {
                $sCounterIdent = 'oxOrder';
                $iCnt = oxNew(Counter::class)->getNext($sCounterIdent);
                $oOrder->oxorder__oxordernr = new Field($iCnt);
            }
            $oOrder->save();
        }
    }
}
