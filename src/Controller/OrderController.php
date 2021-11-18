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

use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;

class OrderController extends OrderController_parent
{
    protected $blSepaMandateConfirm;

    public function getUnzerPubKey()
    {
        return UnzerHelper::getShopPublicKey();
    }

    public function getShopCompanyName()
    {
        return Registry::getConfig()->getActiveShop()->getFieldData('oxcompany');
    }

    protected function _validateTermsAndConditions() // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        if ($this->getPayment()->getId() === 'oscunzer_sepa') {
            $oConfig = Registry::getConfig();

            $blSepaMandateConfirm = $oConfig->getRequestParameter('oscunzersepaagreement');
            if (!$blSepaMandateConfirm) {
                $blValid = false;
            }
        }

        $blValid &= parent::_validateTermsAndConditions();
        return $blValid;
    }
}
