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
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use UnzerSDK\Resources\CustomerFactory;
use UnzerSDK\Resources\PaymentTypes\SepaDirectDebit;
use UnzerSDK\Unzer;

class Sepa extends UnzerPayment
{
    /**
     * @var string
     */
    protected $sIban;

    /**
     * @var mixed|Payment
     */
    protected $_oPayment;

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

    public function validate()
    {
        $oUnzer = UnzerHelper::getUnzer();

        try {
            $uzrSepa = new SepaDirectDebit();
            $sepa = $oUnzer->createPaymentType($uzrSepa);

            $oUser = UnzerHelper::getUser();
            $oBasket = UnzerHelper::getBasket();
            $customer = CustomerFactory::createCustomer($oUser->oxuser__oxfname->value, $oUser->oxuser__oxlname->value);
            $this->setCustomerData($customer, $oUser);
            $orderId = 'o' . str_replace(['0.', ' '], '', microtime(false));

            $transaction = $sepa->charge($oBasket->getPrice()->getPrice(), $oBasket->getBasketCurrency()->name, UnzerHelper::redirectUrl(self::CONTROLLER_URL), $customer, $orderId);


        } catch (\Exception $ex) {

        }
    }
}
