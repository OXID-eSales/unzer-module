<?php

/**
 * This Software is the property of OXID eSales and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * @copyright 2003-2021 OXID eSales AG
 * @author    OXID Solution Catalysts
 * @link      https://www.oxid-esales.com
 */

namespace OxidSolutionCatalysts\Unzer\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Service\Transaction;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;

class DispatcherController extends FrontendController
{
    use ServiceContainer;

    /**
     * @param string $paymentid
     * @return void
     */
    public function updatePaymentTransStatus(): void
    {
        $result = '';

        if ($paymentId = Registry::getRequest()->getRequestParameter('paymentid')) {
            $transaction = $this->getServiceFromContainer(Transaction::class);
            $order = oxNew(Order::class);
            $data = $transaction->getTransactionDataByPaymentId($paymentId);

            $unzerPayment = $this->getServiceFromContainer(UnzerSDKLoader::class)
                ->getUnzerSDK()
                ->fetchPayment($paymentId);

            if ($order->load($data[0]['OXORDERID']) && $unzerPayment->getState() == 1) {
                $utilsDate = Registry::getUtilsDate();
                $date = date('Y-m-d H:i:s', $utilsDate->getTime());
                $order->oxorder__oxpaid = new Field($date);
                $order->save();
            }

            $translator = $this->getServiceFromContainer(Translator::class);

            if ($transaction->writeTransactionToDB($data[0]['OXORDERID'], $data[0]['OXUSERID'], $unzerPayment)) {
                $result = sprintf(
                    $translator->translate('oscunzer_TRANSACTION_CHANGE'),
                    $unzerPayment->getStateName(),
                    $paymentId
                );
            } else {
                $result = $translator->translate('oscunzer_TRANSACTION_NOTHINGTODO') . $paymentId;
            }
        }
        Registry::getUtils()->showMessageAndExit($result);
    }
}
