<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Core;

use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Registry;

class Config extends Config_parent
{
    /**
     * @inerhitDoc
     *
     * @return void
     */
    public function setActShopCurrency($cur)
    {

        if (/** @var string $paymentid */
            $paymentid = Registry::getSession()->getVariable('paymentid')) {
            /** @var \OxidSolutionCatalysts\Unzer\Model\Payment $oPayment */
            $oPayment = oxNew(Payment::class);
            if ($oPayment->load($paymentid) && $oPayment->isUnzerPayment() && !$oPayment->isUnzerPaymentTypeAllowed()) {
                Registry::getSession()->deleteVariable('paymentid');
            }
        }

        parent::setActShopCurrency($cur);
    }
}
