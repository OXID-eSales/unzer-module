<?php

namespace OxidSolutionCatalysts\Unzer\Controller\Admin;

use OxidSolutionCatalysts\Unzer\Service\Transaction as TransactionService;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
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
            if (
                $oPayment->load($oOrder->oxorder__oxpaymenttype->value) &&
                $oPayment->isUnzerSecuredPayment()
            ) {
                $transactionService = $this->getServiceFromContainer(TransactionService::class);
                $sPaymentId = $transactionService->getPaymentIdByOrderId($this->getEditObjectId())[0]['TYPEID'];

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
                        $sInvoiceNr = $oOrder->getUnzerInvoiceNr();
                        try {
                            $transactionService->writeTransactionToDB($oOrder->getId(),
                                $oOrder->oxorder__oxuserid->value, $unzerPayment, $unzerPayment->ship($sInvoiceNr));
                        } catch (\Exception $e) {
                            // TODO Logging
                        }
                    }
                }
            }
        }
    }
}
