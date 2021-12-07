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

use Exception;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\DisplayError;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\ShopVersion;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\Facts\Facts;
use OxidSolutionCatalysts\Unzer\Model\Payments\UnzerPayment;
use OxidSolutionCatalysts\Unzer\Model\Transaction;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\EmbeddedResources\BasketItem;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Resources\TransactionTypes\Charge;

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
        return \OxidSolutionCatalysts\Unzer\Module::MODULE_ID;
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

        //Remove temporary order
        $oOrder = oxNew(Order::class);
        if ($oOrder->load($oSession->getVariable('sess_challenge'))) {
            $oOrder->delete();
        }

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
    public static function redirecturl($destination, $blWithSessionId = false): string
    {
        // redirect to payment-selection page:
        $oSession = Registry::getSession();
        $dstUrl = Registry::getConfig()->getShopCurrentUrl();
        $destination = str_replace('?', '&', $destination);
        $dstUrl .= 'cl=' . $destination;

        if ($blWithSessionId) {
            $dstUrl .= '&force_sid=' . $oSession->getId();
        }

        return $oSession->processUrl($dstUrl);
    }

    /**
     * @param Charge $transaction
     * @return string
     */
    public static function getBankData(Charge $transaction): string
    {
        $amount = Registry::getLang()->formatCurrency($transaction->getAmount());

        $bankData = sprintf(
            Registry::getLang()->translateString('OSCUNZER_BANK_DETAILS_AMOUNT'),
            $amount,
            Registry::getConfig()->getActShopCurrencyObject()->sign
        );

        $bankData .= sprintf(
            Registry::getLang()->translateString('OSCUNZER_BANK_DETAILS_HOLDER'),
            $transaction->getHolder()
        );

        $bankData .= sprintf(
            Registry::getLang()->translateString('OSCUNZER_BANK_DETAILS_IBAN'),
            $transaction->getIban()
        );

        $bankData .= sprintf(
            Registry::getLang()->translateString('OSCUNZER_BANK_DETAILS_BIC'),
            $transaction->getBic()
        );

        $bankData .= sprintf(
            Registry::getLang()->translateString('OSCUNZER_BANK_DETAILS_DESCRIPTOR'),
            $transaction->getDescriptor()
        );

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
     * @param User $oUser
     * @throws UnzerApiException
     */
    public static function writeTransactionToDB(string $orderid, User $oUser)
    {
        $oTrans = oxNew(Transaction::class);

        $unzerPayment = self::getInitialUnzerPayment();
        $unzerCustomer = $unzerPayment->getCustomer();

        $metadata = $unzerPayment->getMetadata();

        $aParams['oscunzertransaction__oxorderid'] = $orderid;
        $aParams['oscunzertransaction__oxshopid'] = Registry::getConfig()->getShopId();
        $aParams['oscunzertransaction__oxuserid'] = $oUser->getId();
        $aParams['oscunzertransaction__amount'] = $unzerPayment->getAmount()->getTotal();
        $aParams['oscunzertransaction__currency'] = $unzerPayment->getCurrency();
        $aParams['oscunzertransaction__typeid'] = $unzerPayment->getId();
        if ($metadata) {
            $aParams['oscunzertransaction__metadataid'] = $metadata->getId();
            $aParams['oscunzertransaction__metadata'] = $metadata->jsonSerialize();
        }
        if ($unzerCustomer) {
            $aParams['oscunzertransaction__customerid'] = $unzerCustomer->getId();
        }
        $aParams['oscunzertransaction__oxactiondate'] = date('Y-m-d H:i:s', Registry::getUtilsDate()->getTime());
        $aParams['oscunzertransaction__oxaction'] = $unzerPayment->getStateName();

        $oTrans->assign($aParams);
        $oTrans->save();
    }

    /**
     * @return Payment|null
     * @throws UnzerApiException
     */
    public static function getInitialUnzerPayment(): ?Payment
    {
        if ($paymentId = Registry::getSession()->getVariable('PaymentId')) {
            /** @var \OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader $unzerSDKLoader */
            $unzerSDKLoader = ContainerFactory::getInstance()
                ->getContainer()
                ->get(\OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader::class);
            $unzer = $unzerSDKLoader->getUnzerSDK();

            return $unzer->fetchPayment($paymentId);
        }

        return null;
    }
}
