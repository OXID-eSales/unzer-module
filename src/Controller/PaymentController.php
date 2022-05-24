<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Controller;

use OxidSolutionCatalysts\Unzer\Core\UnzerDefinitions;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;

class PaymentController extends PaymentController_parent
{
    use ServiceContainer;

    /**
     * @return bool
     */
    public function doSomething(): bool
    {
        return true;
    }

    /**
     * Template variable getter. Returns paymentlist
     *
     * @return array<array-key, mixed>|object
     */
    public function getPaymentList()
    {
        $paymentList = (array)parent::getPaymentList();

        $pubKey = $this->getServiceFromContainer(ModuleSettings::class)->getShopPublicKey();
        $privKey = $this->getServiceFromContainer(ModuleSettings::class)->getShopPrivateKey();
        $registeredWebhook = $this->getServiceFromContainer(ModuleSettings::class)->getRegisteredWebhook();

        if (!$pubKey || !$privKey || !$registeredWebhook) {
            $paymentListRaw = $paymentList;
            $paymentList = [];

            foreach ($paymentListRaw as $key => $payment) {
                if ($payment->isUnzerPayment()) {
                    continue;
                }
                $paymentList[$key] = $payment;
            }
        } else {
            // check ApplePay Eligibility
            if (!$this->getServiceFromContainer(ModuleSettings::class)->isApplePayEligibility()) {
                unset($paymentList[UnzerDefinitions::APPLEPAY_UNZER_PAYMENT_ID]);
            }
        }

        return $paymentList;
    }
}
