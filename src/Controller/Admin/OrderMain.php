<?php

namespace OxidSolutionCatalysts\Unzer\Controller\Admin;

use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Application\Model\Payment;
use OxidSolutionCatalysts\Unzer\Model\Order;
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
        $oOrder = oxNew(Order::class);
        if ($oOrder->load($sOxid)) {
            /** @var \OxidSolutionCatalysts\Unzer\Model\Payment $oPayment */
            $oPayment = oxNew(Payment::class);
            /** @var string $paymentType */
            $paymentType = $oOrder->getFieldData('oxpaymenttype');
            if (
                $oPayment->load($paymentType) &&
                $oPayment->isUnzerSecuredPayment()
            ) {
                $this->sendShipmentNotification($oOrder);
            }
        }
    }

    /**
     * @param Order $oOrder
     * @return void
     */
    public function sendShipmentNotification(Order $oOrder): void
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
