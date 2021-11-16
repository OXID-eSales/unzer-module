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

namespace OxidSolutionCatalysts\Unzer\Model\Payments;

use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use UnzerSDK\Resources\PaymentTypes\SepaDirectDebit;
use UnzerSDK\Resources\AbstractUnzerResource;
use UnzerSDK\Resources\TransactionTypes\Charge;

class Sepa extends UnzerPayment
{
    /**
     * @var string
     */
    protected string $sIban;

    public function __construct($oxpaymentid)
    {
        $oPayment = oxNew(Payment::class);
        $oPayment->load($oxpaymentid);
        $this->_oPayment = $oPayment;
    }

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

    public function getID(): string
    {
        return $this->_oPayment->getId();
    }

    /**
     * @return string
     */
    public function getPaymentProcedure(): string
    {
        return $this->_oPayment->oxpayment__oxpaymentprocedure->value;
    }

    private function getPaymentParams()
    {
        $jsonobj = Registry::getRequest()->getRequestParameter('paymentData');
        $blubb = json_decode($jsonobj);
        return $blubb;
    }

    /**
     * @return   string|void
     */
    private function getUzrIban()
    {
        if (array_key_exists('iban', $this->getPaymentParams())) {
            return $this->getPaymentParams()->iban;
        } else {
            // TODO Translate Error/OXMULTILANG
            UnzerHelper::redirectOnError('order', 'Ungültige Iban');
        }
    }

    /**
     * @return bool
     */
    public function isRecurringPaymentType(): bool
    {
        return true;
    }

    public function execute()
    {
        try {
            $oUnzer = UnzerHelper::getUnzer();

            $sIban = $this->getUzrIban();
            $uzrSepa = new SepaDirectDebit($sIban);

            // TODO Wieso muss Bic und Holder angegeben werden, in Demo nicht enthalten.
            // Es gibt aber einen Fehler invalid bankaccount blaba wenn Bic oder Holder nicht gesetzt
            $uzrSepa->setBic('TESTDETT421');
            $uzrSepa->setHolder('chiptanscatest2');
            $sepa = $oUnzer->createPaymentType($uzrSepa);
            $oBasket = UnzerHelper::getBasket();

            $orderId = 'o' . str_replace(['0.', ' '], '', microtime(false));

          /* @var Charge|AbstractUnzerResource $transaction */
         $transaction = $sepa->charge($oBasket->getPrice()->getPrice(), $oBasket->getBasketCurrency()->name, UnzerHelper::redirecturl(self::CONTROLLER_URL), null, $orderId);
//           // You'll need to remember the shortId to show it on the success or failure page
            Registry::getSession()->setVariable('ShortId', $transaction->getShortId());
            Registry::getSession()->setVariable('PaymentId', $transaction->getPaymentId());

        } catch (\Exception $ex) {
            UnzerHelper::redirectOnError(self::CONTROLLER_URL, $ex->getMessage());
        }
    }
}
