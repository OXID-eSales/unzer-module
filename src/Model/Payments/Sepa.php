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

namespace OxidSolutionCatalysts\Unzer\Model\Payments;

use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use UnzerSDK\Resources\PaymentTypes\SepaDirectDebit;
use UnzerSDK\Resources\AbstractUnzerResource;
use UnzerSDK\Resources\TransactionTypes\Charge;

class Sepa extends Payment
{
    /**
     * @var string
     */
    protected $sPaymentId = 'oscunzer_sepa';

    /**
     * @var string
     */
    protected $sIban;

    /**
     * @return string
     */
    public function getSIban(): string
    {
        return $this->sIban;
    }

    /**
     * @param string $sIban
     */
    public function setSIban(string $sIban): void
    {
        $this->sIban = $sIban;
    }

    public function getPaymentMethod(): string
    {
       return "sepa";
    }

    public function getPaymentCode(): string
    {
        return "sepa";
    }

    public function getSyncMode(): string
    {
        return "sepa";
    }

    public function getID(): string
    {
        return $this->sPaymentId;
    }

    private function getPaymentParams()
    {
        if (!$this->aPaymentParams) {
            $jsonobj = Registry::getRequest()->getRequestParameter('paymentTypeId');
            $this->aPaymentParams = json_decode($jsonobj);
        }
        return $this->aPaymentParams;
    }

    private function getUzrIban()
    {
        if (array_key_exists('iban', $this->getPaymentParams())) {
            return $this->getPaymentParams()->iban;
        } else {
            // TODO Translate Error/OXMULTILANG
            UnzerHelper::redirectOnError('order', 'Ungültige Iban');
        }
    }

    public function validate()
    {
        try {
            $oUnzer = UnzerHelper::getUnzer();
            $sIban = $this->getUzrIban();
            $uzrSepa = new SepaDirectDebit($sIban);
            $sepa = $oUnzer->createPaymentType($uzrSepa);

            $oBasket = $this->getBasket();

            $orderId = 'o' . str_replace(['0.', ' '], '', microtime(false));

            /* @var Charge|AbstractUnzerResource $transaction */
            $transaction = $sepa->charge($oBasket->getPrice()->getPrice(), $oBasket->getBasketCurrency()->name, UnzerHelper::redirectUrl(self::CONTROLLER_URL), null, $orderId);
            //TODO Weitere Verarbeitung, Prüfung $transaction->getMessage , ->getError, ->isSuccess => return $transaction; ?
        } catch (\Exception $ex) {
            UnzerHelper::redirectOnError(self::CONTROLLER_URL, $ex->getMessage());
        }
    }
}
