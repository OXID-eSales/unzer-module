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

use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Registry;

class Config extends Config_parent
{
    /**
     * Sets the actual currency and deletes the paymentid sessions var if the selected currency is not supported by the selected unter payment method
     *
     * @param int $cur 0 = EUR, 1 = GBP, 2 = CHF
     */
    public function setActShopCurrency($cur)
    {
        if ($paymentid = Registry::getSession()->getVariable('paymentid')) {
            $oPayment = oxNew(Payment::class);
            if ($oPayment->load($paymentid) && $oPayment->isUnzerPayment() && !$oPayment->isUnzerPaymentTypeAllowed()) {
                Registry::getSession()->deleteVariable('paymentid');
            }
        }

        parent::setActShopCurrency($cur);
    }
}
