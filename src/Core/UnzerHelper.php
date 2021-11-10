<?php
/**
 * This file is part of OXID eSales Unzer module.
 *
 * OXID eSales Unzer module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales Unzer module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales Unzer module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright 2003-2021 OXID eSales AG
 * @link      http://www.oxid-esales.com
 * @author    OXID Solution Catalysts
 */

namespace OxidSolutionCatalysts\Unzer\Core;

use OxidEsales\Eshop\Core\DisplayError;
use OxidEsales\Eshop\Core\Registry;
use UnzerSDK\Unzer;

class UnzerHelper
{
    /**
     * @return string
     */
    public static function getModuleId(): string
    {
        return 'osc-unzer';
    }

    public static function addErrorToDisplay($errorMsg)
    {
        // TODO Translate Errors
        $oDisplayError = oxNew(DisplayError::class);
        $oDisplayError->setMessage($errorMsg);
        Registry::getUtilsView()->addErrorToDisplay($oDisplayError);
    }

    public static function redirectOnError($destination, $unzerErrorMessage = null)
    {
        self::addErrorToDisplay($unzerErrorMessage);

        // redirect to payment-selection page:
        $oSession = Registry::getSession();
        $dstUrl = Registry::getConfig()->getShopCurrentUrl();
        $dstUrl .= '&cl=' . $destination;

        $dstUrl = $oSession->processUrl($dstUrl);
        Registry::getUtils()->redirect($dstUrl);
        exit;
    }

    public static function getConfigBool($sVarName, $bDefaultValue = false): bool
    {
        $rv = self::getConfigParam($sVarName, $bDefaultValue);
        if ($rv) {
            return true;
        }

        return false;
    }

    public static function getConfigParam($sVarName, $defaultValue = null)
    {
        $oConfig = Registry::getConfig();
        $rv = $oConfig->getShopConfVar($sVarName, null, 'module:' . self::getModuleId());
        if ($rv !== null) {
            return $rv;
        }
        return $defaultValue;
    }

    public static function getShopPublicKey()
    {
        return self::getConfigParam('UnzerPublicKey');
    }

    public static function getShopPrivateKey()
    {
        return self::getConfigParam('UnzerPrivateKey');
    }


    public static function validateSettings(): bool
    {
        return self::getShopPublicKey() !== '' && self::getShopPrivateKey() !== '';
    }

    /**
     * Create object UnzerSDK\Unzer with priv-Key
     *
     * @return Unzer|null
     */
    public static function getUnzer(): ?Unzer
    {
        if (self::validateSettings()) {
            return oxNew(Unzer::class, self::getShopPrivateKey());
        }
        return null;
    }

    /**
     * @return string
     */
    public function getUnzerSystemMode(): string
    {
        $SystemMode = self::getConfigParam('UnzerSystemMode');
        if ($SystemMode) {
            return "production";
        } else {
            return "sandbox";
        }
    }

    /**
     * @param string $url
     */
    public function redirect(string $url)
    {
        Registry::getUtils()->redirect($url, true, 302);
    }

    /**
     * @param string
     * @return string
     */
    public function redirectUrl($cl): string
    {
        return Registry::getConfig()->getShopHomeUrl() . 'cl=' . $cl;
    }
}
