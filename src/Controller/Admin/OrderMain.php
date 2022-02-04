<?php

namespace OxidSolutionCatalysts\Unzer\Controller\Admin;

use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use UnzerSDK\Exceptions\UnzerApiException;

class OrderMain extends OrderMain_parent
{
    use ServiceContainer;

    /**
     * Method is used for overriding.
     *
     * @return void
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
                $this->sendShipmentNotification($oOrder);
            }
        }
    }

    public function sendShipmentNotification(\OxidEsales\Eshop\Application\Model\Order $oOrder): void
    {
        $paymentService = $this->getServiceFromContainer(\OxidSolutionCatalysts\Unzer\Service\Payment::class);
        $oShipment = $paymentService->sendShipmentNotification($oOrder);
        if ($oShipment instanceof UnzerApiException) {
            $oxException = oxNew(
                StandardException::class,
                $oShipment->getMessage(),
                $oShipment->getCode(),
                $oShipment
            );

            Registry::getUtilsView()->addErrorToDisplay($oxException);
        }
    }
}
