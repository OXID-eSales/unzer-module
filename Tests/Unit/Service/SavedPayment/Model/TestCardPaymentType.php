<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service\SavedPayment\Model;

use UnzerSDK\Resources\PaymentTypes\Card;

/**
 * the unzer sdk magically sets the brand of the card payment Type: Card
 * I introduze this class only for test purposes to be able to test
 * SavedPaymentFetchPaymentTypeService::fetchPaymentTypes
 */
class TestCardPaymentType extends Card
{
    private string $brand;
    public function __construct(?string $number, ?string $expiryDate, string $email = null, string $brand = null)
    {
        parent::__construct($number, $expiryDate, $email);
        $this->brand = $brand;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }
}
