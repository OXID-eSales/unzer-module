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

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidSolutionCatalysts\Unzer\Interfaces\ClassMapping\ClassMappingInterface;

class DispatcherController extends FrontendController implements ClassMappingInterface
{
    /**
     * @param string $paymentid
     */
    public function executePayment(string $paymentid)
    {
        $oUnzerPayment = oxNew(self::UNZERCLASSNAMEMAPPING[$paymentid], $paymentid);
        $oUnzerPayment->execute();
        $oUnzerPayment->checkpaymentstatus();
    }
}
