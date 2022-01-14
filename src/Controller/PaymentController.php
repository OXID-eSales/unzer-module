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

use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;

class PaymentController extends PaymentController_parent
{
    use ServiceContainer;

    /**
     * @inheritDoc
     */
    public function getPaymentList()
    {
        $aPaymentList = parent::getPaymentList();

        $pubKey = $this->getServiceFromContainer(ModuleSettings::class)->getShopPublicKey();
        $privKey = $this->getServiceFromContainer(ModuleSettings::class)->getShopPrivateKey();

        if (!$pubKey && !$privKey) {
            foreach ($aPaymentList as $key => $oPayment) {
                if ($oPayment->isUnzerPayment()) {
                    unset($aPaymentList[$key]);
                }
            }
        }

        return $aPaymentList;
    }
}
