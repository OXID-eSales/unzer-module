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

use D3\ModCfg\Application\Model\Exception\d3_cfg_mod_exception;
use D3\ModCfg\Application\Model\Exception\d3ShopCompatibilityAdapterException;
use Doctrine\DBAL\DBALException;
use Exception;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\DisplayError;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\ShopVersion;
use OxidEsales\Facts\Facts;
use OxidSolutionCatalysts\Unzer\Model\Payments\UnzerPayment;
use OxidSolutionCatalysts\Unzer\Model\Transaction;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\EmbeddedResources\Message;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Resources\TransactionTypes\AbstractTransactionType;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\Unzer;
use UnzerSDK\Validators\PrivateKeyValidator;
use UnzerSDK\Validators\PublicKeyValidator;

class UnzerHelper
{
    /**
     * @return array
     */
    public static function getRDFinserts(): array
    {
        return [
            'oscunzer_card_mastercard' => [
                'oxpaymentid' => 'oscunzer_card',
                'oxobjectid' => 'MasterCard',
                'oxtype' => 'rdfapayment',
            ],
            'oscunzer_card_visa' => [
                'oxpaymentid' => 'oscunzer_card',
                'oxobjectid' => 'VISA',
                'oxtype' => 'rdfapayment',
            ],
            'oscunzer_card_americanexpress' => [
                'oxpaymentid' => 'oscunzer_card',
                'oxobjectid' => 'AmericanExpress',
                'oxtype' => 'rdfapayment',
            ],
            'oscunzer_card_dinersclub' => [
                'oxpaymentid' => 'oscunzer_card',
                'oxobjectid' => 'DinersClub',
                'oxtype' => 'rdfapayment',
            ],
            'oscunzer_card_jcb' => [
                'oxpaymentid' => 'oscunzer_card',
                'oxobjectid' => 'JCB',
                'oxtype' => 'rdfapayment',
            ],
            'oscunzer_prepayment' => [
                'oxpaymentid' => 'oscunzer_prepayment',
                'oxobjectid' => 'ByBankTransferInAdvance',
                'oxtype' => 'rdfapayment',
            ],
            'oscunzer_pis' => [
                'oxpaymentid' => 'oscunzer_pis',
                'oxobjectid' => 'ByBankTransferInAdvance',
                'oxtype' => 'rdfapayment',
            ],
            'oscunzer_invoice' => [
                'oxpaymentid' => 'oscunzer_invoice',
                'oxobjectid' => 'ByInvoice',
                'oxtype' => 'rdfapayment',
            ],
            'oscunzer_invoice-secured' => [
                'oxpaymentid' => 'oscunzer_invoice-secured',
                'oxobjectid' => 'ByInvoice',
                'oxtype' => 'rdfapayment',
            ],
            'oscunzer_sepa' => [
                'oxpaymentid' => 'oscunzer_sepa',
                'oxobjectid' => 'DirectDebit',
                'oxtype' => 'rdfapayment',
            ],
            'ooscunzer_sepa-secured' => [
                'oxpaymentid' => 'oscunzer_sepa-secured',
                'oxobjectid' => 'DirectDebit',
                'oxtype' => 'rdfapayment',
            ],
            'oscunzer_paypal' => [
                'oxpaymentid' => 'oscunzer_paypal',
                'oxobjectid' => 'PayPal',
                'oxtype' => 'rdfapayment',
            ],
        ];
    }

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

    /**
     * @param $destination
     * @return string
     */
    public static function redirecturl($destination): string
    {
        // redirect to payment-selection page:
        $oSession = Registry::getSession();
        $dstUrl = Registry::getConfig()->getShopCurrentUrl();
        $dstUrl .= '&cl=' . $destination;

        return $oSession->processUrl($dstUrl);
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
        return PrivateKeyValidator::validate(self::getShopPrivateKey()) && PublicKeyValidator::validate(self::getShopPublicKey());
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
    public static function getUnzerSystemMode(): string
    {
        $SystemMode = self::getConfigParam('UnzerSystemMode');
        if ($SystemMode) {
            return "production";
        } else {
            return "sandbox";
        }
    }

    /**
     * @return object|Basket|null
     */
    public static function getBasket()
    {
        $oSession = Registry::getSession();
        return $oSession->getBasket();
    }

    /**
     * @return false|User|null
     */
    public static function getUser()
    {
        $oSession = Registry::getSession();
        return $oSession->getUser();
    }

    /**
     * @param Charge $transaction
     * @return string
     */
    public static function getBankData(Charge $transaction): string
    {
        $amount = Registry::getLang()->formatCurrency($transaction->getAmount());
        $bankData = sprintf(Registry::getLang()->translateString('OSCUNZER_BANK_DETAILS_AMOUNT'), $amount, Registry::getConfig()->getActShopCurrencyObject()->sign);
        $bankData .= sprintf(Registry::getLang()->translateString('OSCUNZER_BANK_DETAILS_HOLDER'), $transaction->getHolder());
        $bankData .= sprintf(Registry::getLang()->translateString('OSCUNZER_BANK_DETAILS_IBAN'), $transaction->getIban());
        $bankData .= sprintf(Registry::getLang()->translateString('OSCUNZER_BANK_DETAILS_BIC'), $transaction->getBic());
        $bankData .= sprintf(Registry::getLang()->translateString('OSCUNZER_BANK_DETAILS_DESCRIPTOR'), $transaction->getDescriptor());

        return $bankData;
    }

    /**
     * @param int|string $code
     * @param string $callback
     * @return array|string
     */
    public static function translatedMsg($code, string $callback)
    {
        $string = 'oscunzer_' . substr((string)$code, 4);
        $oLang = Registry::getLang();
        $translation = $oLang->translateString($string);
        if (!$oLang->isTranslated()) {
            $translation = $callback;
        }

        return $translation;
    }

    /**
     * @param string $orderid
     * @throws UnzerApiException
     */
    public static function writeTransactionToDB(string $orderid)
    {
        $oTrans = oxNew(Transaction::class);

        $unzerPayment = self::getInitialUnzerPayment();
        $unzerCustomer = $unzerPayment->getCustomer();
        $oUser = self::getUser();
        $metadata = $unzerPayment->getMetadata();

        $oTrans->oscunzertransaction__oxorderid = new Field($orderid);
        $oTrans->oscunzertransaction__oxshopid = new Field(Registry::getConfig()->getShopId());
        $oTrans->oscunzertransaction__oxuserid = new Field($oUser->getId());
        $oTrans->oscunzertransaction__amount = new Field($unzerPayment->getAmount()->getTotal());
        $oTrans->oscunzertransaction__currency = new Field($unzerPayment->getCurrency());
        $oTrans->oscunzertransaction__typeid = new Field($unzerPayment->getId());
        if ($metadata) {
            $oTrans->oscunzertransaction__metadataid = new Field($metadata->getId());
            $oTrans->oscunzertransaction__metadata = new Field($metadata->jsonSerialize());
        }
        $oTrans->oscunzertransaction__customerid = new Field($unzerCustomer->getId());
        $oTrans->oscunzertransaction__oxactiondate = new Field(date('Y-m-d H:i:s', \OxidEsales\Eshop\Core\Registry::getUtilsDate()->getTime()));
        $oTrans->oscunzertransaction__oxaction = new Field($unzerPayment->getStateName());
        $oTrans->save();
    }

    /**
     * @return Payment|null
     * @throws UnzerApiException
     */
    public static function getInitialUnzerPayment(): ?Payment
    {
        if ($paymentId = Registry::getSession()->getVariable('PaymentId')) {
            $unzer = self::getUnzer();
            return $unzer->fetchPayment($paymentId);
        }

        return null;
    }

    /**
     * @param UnzerPayment $UnzerPayment
     * @return Metadata
     * @throws Exception
     */
    public static function getMetadata(UnzerPayment $UnzerPayment): Metadata
    {
        $metadata = new Metadata();
        $metadata->setShopType("Oxid eShop " . (new Facts)->getEdition());
        $metadata->setShopVersion(ShopVersion::getVersion());
        $metadata->addMetadata('shopid', (string) Registry::getConfig()->getShopId());
        $metadata->addMetadata('paymentmethod', $UnzerPayment->getPaymentMethod());
        $metadata->addMetadata('paymentprocedure', $UnzerPayment->getPaymentProcedure());

        return $metadata;
    }
}
