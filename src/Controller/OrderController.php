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

use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;

class OrderController extends OrderController_parent
{
    /**
     * @return bool|mixed|object|null
     */
    public function getUnzerPubKey()
    {
        return UnzerHelper::getShopPublicKey();
    }
}
