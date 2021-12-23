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
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidSolutionCatalysts\Unzer\Service\Transaction;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;

class DispatcherController extends FrontendController
{
    /**
     * @param string $paymentid
     * @return void
     */
    public function updatePaymentTransStatus(): void
    {
        $result = '';

        if ($paymentId = Registry::getRequest()->getRequestParameter('paymentid')) {
            $container = ContainerFactory::getInstance()->getContainer();
            /** @var Transaction $transaction */
            $transaction = $container->get(Transaction::class);
            $order = oxNew(Order::class);
            $data = $transaction->getTransactionDataByPaymentId($paymentId);
            /** @var \UnzerSDK\Resources\Payment $unzerPayment */
            $unzerPayment = $container->get(UnzerSDKLoader::class)->getUnzerSDK()->fetchPayment($paymentId);
            if ($order->load($data[0]['OXORDERID']) && $unzerPayment->getState() == 1) {
                $utilsDate = Registry::getUtilsDate();
                $date = date('Y-m-d H:i:s', $utilsDate->getTime());
                $order->oxorder__oxpaid = new Field($date);
                $order->save();
            }

            /** @var Translator $translator */
            $translator = $container->get(Translator::class);

            if ($transaction->writeTransactionToDB($data[0]['OXORDERID'], $data[0]['OXUSERID'], $unzerPayment)) {
                $result = sprintf(
                    $translator->translate(
                        'oscunzer_TRANSACTION_CHANGE',
                        'State %s was written to database for payment %s'
                    ),
                    $unzerPayment->getStateName(),
                    $paymentId
                );
            } else {
                $result = $translator->translate(
                    'oscunzer_TRANSACTION_NOTHINGTODO',
                    'No update needed. There was no new state for payment: '
                ) . $paymentId;
            }
        }
        Registry::getUtils()->showMessageAndExit($result);
    }
}
