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

class Sepa extends UnzerPayment
{
    /**
     * @var string
     */
    protected string $sIban;

    /**
     * @var mixed|Payment
     */
    protected $_oPayment;

    /**
     * @var array
     */
    protected array $aPaymentParams;

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
        if (!$this->aPaymentParams) {
            $jsonobj = Registry::getRequest()->getRequestParameter('paymentTypeId');
            $this->aPaymentParams = json_decode($jsonobj);
        }
        return $this->aPaymentParams;
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

    public function validate()
    {
        try {
            $oUnzer = UnzerHelper::getUnzer();
            $sIban = $this->getUzrIban();
            $uzrSepa = new SepaDirectDebit($sIban);
            $sepa = $oUnzer->createPaymentType($uzrSepa);

            $oBasket = UnzerHelper::getBasket();

            $orderId = 'o' . str_replace(['0.', ' '], '', microtime(false));

//            /* @var Charge|AbstractUnzerResource $transaction */
//            $transaction = $sepa->charge($oBasket->getPrice()->getPrice(), $oBasket->getBasketCurrency()->name, self::CONTROLLER_URL, null, $orderId);
//            //TODO Weitere Verarbeitung, Prüfung $transaction->getMessage , ->getError, ->isSuccess => return $transaction; ?
        } catch (\Exception $ex) {
            UnzerHelper::redirectOnError(self::CONTROLLER_URL, $ex->getMessage());
        }
    }
}
