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

use Exception;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use UnzerSDK\Exceptions\UnzerApiException;

class GiroPay extends UnzerPayment
{
    /**
     * @var string
     */
    protected $Paymentmethod = 'giropay';

    /**
     * @var array
     */
    protected $aCurrencies = [];

    /**
     * @return bool
     */
    public function isRecurringPaymentType(): bool
    {
        return false;
    }

    /**
     * @return void
     * @throws UnzerApiException
     * @throws Exception
     */
    public function execute()
    {
        /** @var \UnzerSDK\Resources\PaymentTypes\Giropay $giro */
        $giro = $this->unzerSDK->createPaymentType(new \UnzerSDK\Resources\PaymentTypes\Giropay);

        $customer = $this->getCustomerData();

        $transaction = $giro->charge(
            $this->basket->getPrice()->getPrice(),
            $this->basket->getBasketCurrency()->name,
            UnzerHelper::redirecturl(self::PENDING_URL),
            $customer,
            $this->unzerOrderId,
            $this->getMetadata()
        );

        $this->setSessionVars($transaction);
    }
}
