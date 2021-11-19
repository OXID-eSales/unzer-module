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
    protected $blSepaMandateConfirmError = 0;

    public function getUnzerPubKey()
    {
        return UnzerHelper::getShopPublicKey();
    }

    public function execute() // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        if (!$this->getSession()->checkSessionChallenge()) {
            return;
        }

        if (!$this->_validateTermsAndConditions()) {
            $this->_blConfirmAGBError = 1;

            return;
        }
        if ($this->getPayment()->getId() === 'oscunzer_sepa') {
            $blSepaMandateConfirm = Registry::getRequest()->getRequestParameter('sepaConfirmation');
            if (!$blSepaMandateConfirm) {
                $this->blSepaMandateConfirmError = 1;

                return;
            }
        }

        return parent::execute();
    }

    public function isSepaMandateConfirmationError()
    {
        return $this->blSepaMandateConfirmError;
    }
}
