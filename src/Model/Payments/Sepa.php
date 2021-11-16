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
use UnzerSDK\Resources\CustomerFactory;
use UnzerSDK\Resources\PaymentTypes\SepaDirectDebit;
use UnzerSDK\Resources\AbstractUnzerResource;
use UnzerSDK\Resources\TransactionTypes\Charge;

class Sepa extends UnzerPayment
{
    /**
     * @var string
     */
    protected string $sIban;

    /**
     * @var Payment
     */
    protected Payment $oPayment;


    public function __construct($oxpaymentid)
    {
        $oPayment = oxNew(Payment::class);
        $oPayment->load($oxpaymentid);
        $this->oPayment = $oPayment;
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
        return $this->oPayment->getId();
    }

    /**
     * @return string
     */
    public function getPaymentProcedure(): string
    {
        return $this->oPayment->oxpayment__oxpaymentprocedure->value;
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
     * @return   string|void
     */
    private function getUzrId()
    {
        if (array_key_exists('id', $this->getPaymentParams())) {
            return $this->getPaymentParams()->id;
        } else {
            // TODO Translate Error/OXMULTILANG
            UnzerHelper::redirectOnError('order', 'Ungültige ID');
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
            $sId = $this->getUzrId();
            $uzrSepa = $oUnzer->fetchPaymentType($sId);
            $oBasket = UnzerHelper::getBasket();
            $orderId = 'o' . str_replace(['0.', ' '], '', microtime(false));
            $oUser = UnzerHelper::getUser();
            $oBasket = UnzerHelper::getBasket();
            $customer = CustomerFactory::createCustomer($oUser->oxuser__oxfname->value, $oUser->oxuser__oxlname->value);
            $this->setCustomerData($customer, $oUser);

            $transaction = $uzrSepa->charge($oBasket->getPrice()->getPrice(), $oBasket->getBasketCurrency()->name, UnzerHelper::redirecturl(self::CONTROLLER_URL), $customer, $orderId);
//           // You'll need to remember the shortId to show it on the success or failure page
            Registry::getSession()->setVariable('ShortId', $transaction->getShortId());
            Registry::getSession()->setVariable('PaymentId', $transaction->getPaymentId());
        } catch (\Exception $ex) {
            UnzerHelper::redirectOnError(self::CONTROLLER_URL, $ex->getMessage());
        }
    }
}
