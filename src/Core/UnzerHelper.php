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
use OxidSolutionCatalysts\Unzer\PaymentExtensions\UnzerPayment;
use OxidSolutionCatalysts\Unzer\Model\Transaction;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\EmbeddedResources\BasketItem;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Resources\TransactionTypes\Charge;

class UnzerHelper
{
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
     * @param string $orderid
     * @param User $oUser
     * @throws UnzerApiException
     */
    public static function writeTransactionToDB(string $orderid, User $oUser)
    {
        $oTrans = oxNew(Transaction::class);
        $unzerPayment = self::getInitialUnzerPayment();

        $params = [
            'oxorderid' => $orderid,
            'oxshopid' => Registry::getConfig()->getShopId(),
            'oxuserid' => $oUser->getId(),
            'amount' => $unzerPayment->getAmount()->getTotal(),
            'currency' => $unzerPayment->getCurrency(),
            'typeid' => $unzerPayment->getId(),
            'oxactiondate' => date('Y-m-d H:i:s', Registry::getUtilsDate()->getTime()),
            'oxaction' => $unzerPayment->getStateName(),
        ];

        if ($metadata = $unzerPayment->getMetadata()) {
            $params['metadataid'] = $metadata->getId();
            $params['metadata'] = $metadata->jsonSerialize();
        }

        if ($unzerCustomer = $unzerPayment->getCustomer()) {
            $params['customerid'] = $unzerCustomer->getId();
        }

        $oTrans->assign($params);
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
