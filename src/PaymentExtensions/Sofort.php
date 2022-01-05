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

namespace OxidSolutionCatalysts\Unzer\PaymentExtensions;

use UnzerSDK\Resources\PaymentTypes\BasePaymentType;

class Sofort extends UnzerPayment
{
    protected $paymentMethod = 'sofort';

    protected $allowedCurrencies = ['EUR'];

    /**
     * @return \UnzerSDK\Resources\PaymentTypes\Sofort
     */
    public function getUnzerPaymentTypeObject(): BasePaymentType
    {
        return $this->unzerSDK->createPaymentType(
            new \UnzerSDK\Resources\PaymentTypes\Sofort()
        );
    }
}
