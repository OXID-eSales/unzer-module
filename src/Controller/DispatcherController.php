<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Service\Transaction;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
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
     */
    public function updatePaymentTransStatus(): void
    {
        $result = '';

        $unzer = $this->getServiceFromContainer(UnzerSDKLoader::class)->getUnzerSDK();
        $jsonRequest = file_get_contents('php://input');
        $resource = $unzer->fetchResourceFromEvent($jsonRequest);

        if ($paymentId = $resource->getId()) {
            $transaction = $this->getServiceFromContainer(Transaction::class);
            $order = oxNew(Order::class);
            $data = $transaction->getTransactionDataByPaymentId($paymentId);

            $unzerPayment = $this->getServiceFromContainer(UnzerSDKLoader::class)
                ->getUnzerSDK()
                ->fetchPayment($paymentId);

            if ($order->load($data[0]['OXORDERID'])) {
                if ($unzerPayment->getState() == 1) {
                    $utilsDate = Registry::getUtilsDate();
                    $date = date('Y-m-d H:i:s', $utilsDate->getTime());
                    $order->oxorder__oxpaid = new Field($date);
                    $order->save();
                }

                $translator = $this->getServiceFromContainer(Translator::class);

                if ($order->initWriteTransactionToDB()) {
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
        Registry::getUtils()->showMessageAndExit($result);
    }
}
