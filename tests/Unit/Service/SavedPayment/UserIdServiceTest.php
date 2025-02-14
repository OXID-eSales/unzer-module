<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service\SavedPayment;

use OxidSolutionCatalysts\Unzer\Service\SavedPayment\UserIdService;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Resources\PaymentTypes\Card;
use UnzerSDK\Resources\PaymentTypes\Paypal;
use UnzerSDK\Resources\PaymentTypes\SepaDirectDebit;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class UserIdServiceTest extends TestCase
{
    public function testGetUserIdByPaypal()
    {
        $paypal = $this->createMock(Paypal::class);
        $paypal->method('getEmail')->willReturn('test@example.com');

        $userIdService = new UserIdService();
        $result = $userIdService->getUserIdByPaymentType($paypal);

        $this->assertSame('test@example.com', $result);
    }

    public function testGetUserIdByPaypalWithNullEmail()
    {
        $paypal = $this->createMock(Paypal::class);
        $paypal->method('getEmail')->willReturn(null);

        $userIdService = new UserIdService();
        $result = $userIdService->getUserIdByPaymentType($paypal);

        $this->assertSame('', $result);
    }

    public function testGetUserIdByCard()
    {
        $card = $this->createMock(Card::class);
        $card->method('getNumber')->willReturn('1234567890123456');
        $card->method('getExpiryDate')->willReturn('12/34');

        $userIdService = new UserIdService();
        $result = $userIdService->getUserIdByPaymentType($card);

        $this->assertSame('1234567890123456|12/34', $result);
    }

    public function testGetUserIdBySepaDirectDebit()
    {
        $sepa = $this->createMock(SepaDirectDebit::class);
        $sepa->method('getIban')->willReturn('DE89370400440532013000');

        $userIdService = new UserIdService();
        $result = $userIdService->getUserIdByPaymentType($sepa);

        $this->assertSame('DE89370400440532013000', $result);
    }

    public function testGetUserIdBySepaDirectDebitWithNullIban()
    {
        $sepa = $this->createMock(SepaDirectDebit::class);
        $sepa->method('getIban')->willReturn(null);

        $userIdService = new UserIdService();
        $result = $userIdService->getUserIdByPaymentType($sepa);

        $this->assertSame('', $result);
    }

    public function testInvalidPaymentTypeReturnsEmptyString()
    {
        $invalidPaymentType = $this->createMock(BasePaymentType::class);

        $userIdService = new UserIdService();

        $this->assertEmpty($userIdService->getUserIdByPaymentType($invalidPaymentType));
    }
}
