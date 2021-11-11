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

namespace OxidSolutionCatalysts\Unzer\Core\Extend;

use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;

class ViewConfig extends ViewConfig_parent
{
    /** @var UnzerHelper|null \OxidSolutionCatalysts\Unzer\Core\UnzerHelper */
    protected ?UnzerHelper $UnzerHelper = null;

    /**
     * Returns Unzer config.
     *
     * @return UnzerHelper
     */
    protected function getUnzerHelper(): ?UnzerHelper
    {
        if (is_null($this->UnzerHelper)) {
            $this->UnzerHelper = oxNew(UnzerHelper::class);
        }

        return $this->UnzerHelper;
    }

    /**
     * Returns System Mode live|sandbox.
     *
     * @return string
     */
    public function getUnzerSystemMode(): string
    {
        return $this->getUnzerHelper()->getUnzerSystemMode();
    }

    /**
     * Returns unzer public key.
     *
     * @return string
     */
    public function getUnzerPubKey(): string
    {
        return $this->getUnzerHelper()->getShopPublicKey();
    }

    /**
     * Returns unzer private key.
     *
     * @return string
     */
    public function getUnzerPrivKey(): string
    {
        return $this->getUnzerHelper()->getShopPrivateKey();
    }
}
