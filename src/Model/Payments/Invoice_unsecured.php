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

use RuntimeException;
use UnzerSDK\examples\ExampleDebugHandler;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Unzer;
use UnzerSDK\Resources\CustomerFactory;
use UnzerSDK\Resources\PaymentTypes\Invoice;

class Invoice_unsecured extends Payment
{
    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return 'invoice';
    }

    /**
     * @return string
     */
    public function getPaymentCode(): string
    {
        return 'IV';
    }

    /**
     * @return string
     */
    public function getSyncMode(): string
    {
        return 'SYNC';
    }

    public function validate()
    {
        $unzerHelper = $this->getUnzerHelper();

        // Catch API errors, write the message to your log and show the ClientMessage to the client.
        try {
            // Create an Unzer object using your private key and register a debug handler if you want to.
            $unzer = new Unzer($unzerHelper->getShopPrivateKey());
            $unzer->setDebugMode(true)->setDebugHandler(new ExampleDebugHandler());

            /** @var Invoice $invoice */
            $invoice = $unzer->createPaymentType(new Invoice());

            $oUser = $this->getUser();
            $oBasket = $this->getBasket();

            $customer = CustomerFactory::createCustomer($oUser->oxuser__oxfname->value, $oUser->oxuser__oxlname->value);
            $this->setCustomerData($customer, $oUser);

            $orderId = 'o' . str_replace(['0.', ' '], '', microtime(false));

            $transaction = $invoice->charge($oBasket->getPrice()->getPrice(), $oBasket->getBasketCurrency()->name, $unzerHelper->redirectUrl(self::CONTROLLER_URL), $customer, $orderId);

            // You'll need to remember the shortId to show it on the success or failure page
            $_SESSION['ShortId'] = $transaction->getShortId();
            $_SESSION['PaymentId'] = $transaction->getPaymentId();
            $_SESSION['additionalPaymentInformation'] =
                sprintf(
                    "Please transfer the amount of %f %s to the following account:<br /><br />"
                    . "Holder: %s<br/>"
                    . "IBAN: %s<br/>"
                    . "BIC: %s<br/><br/>"
                    . "<i>Please use only this identification number as the descriptor: </i><br/>"
                    . "%s",
                    $transaction->getAmount(),
                    $transaction->getCurrency(),
                    $transaction->getHolder(),
                    $transaction->getIban(),
                    $transaction->getBic(),
                    $transaction->getDescriptor()
                );

            //TODO SUCCESS CHECK Dummy Redirect
            \OxidEsales\Eshop\Core\Registry::getUtils()->redirect('index.php?cl=order', true, 302);
        } catch (UnzerApiException $e) {
            $merchantMessage = $e->getMerchantMessage();
            $clientMessage = $e->getClientMessage();
        } catch (RuntimeException $e) {
            $merchantMessage = $e->getMessage();
        }
        $unzerHelper->redirect($unzerHelper->redirectUrl(self::FAILURE_URL), $merchantMessage, $clientMessage);
    }
}
