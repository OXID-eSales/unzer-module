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

use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;

class ViewConfig extends ViewConfig_parent
{
    use ServiceContainer;

    /**
     * Returns System Mode live|sandbox.
     *
     * @return string
     */
    public function getUnzerSystemMode(): string
    {
        return $this->getServiceFromContainer(ModuleSettings::class)->getSystemMode();
    }

    /**
     * Returns unzer public key.
     *
     * @return string
     */
    public function getUnzerPubKey(): string
    {
        return $this->getServiceFromContainer(ModuleSettings::class)->getShopPublicKey();
    }

    /**
     * Returns unzer private key.
     *
     * @return string
     */
    public function getUnzerPrivKey(): string
    {
        return $this->getServiceFromContainer(ModuleSettings::class)->getShopPrivateKey();
    }

    /**
     * retrieve additional payment information from session
     *
     * @return string
     */
    public function getSessionPaymentInfo()
    {
        return Registry::getSession()->getVariable('additionalPaymentInformation');
    }
}
