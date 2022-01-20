<?php

namespace OxidSolutionCatalysts\Unzer\Controller\Admin;

use OxidSolutionCatalysts\Unzer\Model\TransactionList;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use PHPUnit\Exception;
use UnzerSDK\Resources\Payment;

class OrderMain extends OrderMain_parent
{
    use ServiceContainer;

    /**
     * Method is used for overriding.
     */
    protected function onOrderSend()
    {
        $sOxid = $this->getEditObjectId();
        $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        if ($oOrder->load($sOxid)) {
            $oPayment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
            if ($oPayment->load($oOrder->oxorder__oxpaymenttype->value)) {
               if ($oPayment->isUnzerSecuredPayment()) {
                   $transactionList = oxNew(TransactionList::class);
                   $transactionList->getTransactionList($this->getEditObjectId());

                   $lTransaction = null;
                   foreach ($transactionList as $transaction) {
                       $lTransaction = $transaction;
                   }

                   if ($lTransaction !== null) {
                       $sPaymentId = $lTransaction->getUnzerTypeId();
                       if ($sPaymentId) {
                           /** @var Payment $unzerPayment */
                           $unzerPayment = $this->getServiceFromContainer(UnzerSDKLoader::class)
                               ->getUnzerSDK()
                               ->fetchPayment($sPaymentId);

                           $blIsShipped = false;
                           foreach ($unzerPayment->getShipments() as $unzShipment) {
                               if ($unzShipment->isSuccess()) {
                                   $blIsShipped = true;
                               }
                           }
                           if (!$blIsShipped) {
                               $sInvoiceNr = $oOrder->getFieldData('OXINVOICENR') == 0 ? 'inv' . $oOrder->getFieldData('OXORDERNR') : $oOrder->getFieldData('OXINVOICENR');
                              try {
                                  $shipment = $unzerPayment->ship($sInvoiceNr);
                              } catch (Exception $e) {
// TODO Logging
                              }


                           }
                       }
                   }
               }
            }
        }
    }
}
