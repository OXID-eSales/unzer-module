<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidSolutionCatalysts\Unzer\Service\Transaction;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use OxidSolutionCatalysts\Unzer\Service\UnzerWebhooks;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use UnzerSDK\Exceptions\UnzerApiException;

class DispatcherController extends FrontendController
{
    use ServiceContainer;

    /**
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws UnzerApiException
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function updatePaymentTransStatus(): void
    {
        $result = '';

        /** @var string $jsonRequest */
        $jsonRequest = file_get_contents('php://input');

        /** @var array $aJson */
        $aJson = json_decode($jsonRequest, true);
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
            ($url['scheme'] != "https" || ($url['host'] != "api.unzer.com" && $url['host'] != "sbx-api.heidelpay.com"))
        ) {
            Registry::getUtils()->showMessageAndExit("No valid retrieveUrl");
        }

        if (!$transaction->isValidTransactionTypeId($typeid)) {
            Registry::getUtils()->showMessageAndExit("Invalid type id");
        }

        $unzer = $this->getServiceFromContainer(UnzerSDKLoader::class)->getUnzerSDKbyKey($unzerKey);
        $resource = $unzer->fetchResourceFromEvent($jsonRequest);

        $paymentId = $resource->getId();
        if (is_string($paymentId)) {
            /** @var \OxidSolutionCatalysts\Unzer\Model\Order $order */
            $order = oxNew(\OxidSolutionCatalysts\Unzer\Model\Order::class);
            /** @var array $data */
            $data = $transaction->getTransactionDataByPaymentId($paymentId);

            $unzerPayment = $unzer->fetchPayment($paymentId);

            if ($order->load($data[0]['OXORDERID'])) {
                /** @var string $oxTransStatus */
                $oxTransStatus = $order->getFieldData('oxtransstatus');
                if ($unzerPayment->getState() == 1 && $oxTransStatus == "OK") {
                    $utilsDate = Registry::getUtilsDate();
                    $date = date('Y-m-d H:i:s', $utilsDate->getTime());
                    $order->setFieldData('oxpaid', $date);
                    $order->save();
                }

                if ($unzerPayment->getState() == 2) {
                    $order->cancelOrder();
                }

                $translator = $this->getServiceFromContainer(Translator::class);

                if ($unzerPayment->getState() != 2 && $oxTransStatus != "OK") {
                    $ret = $order->reinitializeOrder();
                    if ($ret != 1) {
                        $unzer->debugLog("Order-Recalculation failed and returned with code: " . $ret);
                    }
                }

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
            }
        }
        $transaction->cleanUpNotFinishedOrders();

        Registry::getUtils()->showMessageAndExit($result);
    }
}
