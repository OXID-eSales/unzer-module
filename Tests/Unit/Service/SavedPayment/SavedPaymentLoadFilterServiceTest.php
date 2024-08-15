<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service\SavedPayment;

use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentLoadFilterService;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentMethodValidator;
use OxidSolutionCatalysts\Unzer\Service\SavedPaymentLoadService;
use PHPUnit\Framework\TestCase;

class SavedPaymentLoadFilterServiceTest extends TestCase
{
    /** @var SavedPaymentLoadFilterService */
    private $filterService;

    /** @var SavedPaymentMethodValidator|\PHPUnit\Framework\MockObject\MockObject */
    private $savedPaymentMethodValidator;

    protected function setUp(): void
    {
        $this->savedPaymentMethodValidator = $this->createMock(SavedPaymentMethodValidator::class);
        $this->filterService = new SavedPaymentLoadFilterService($this->savedPaymentMethodValidator);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentLoadFilterService::getPaymentTypeIdLikeExpression
     */
    public function testGetPaymentTypeIdLikeExpressionValidMethodAll()
    {
        $this->savedPaymentMethodValidator
            ->method('validate')
            ->with(SavedPaymentLoadService::SAVED_PAYMENT_ALL)
            ->willReturn(true);

        $result = $this->filterService->getPaymentTypeIdLikeExpression(SavedPaymentLoadService::SAVED_PAYMENT_ALL);

        $expected = "transactionAfterOrder.PAYMENTTYPEID LIKE 's-"
            . SavedPaymentLoadService::SAVED_PAYMENT_PAYPAL . "%'"
            . " OR transactionAfterOrder.PAYMENTTYPEID LIKE 's-"
            . SavedPaymentLoadService::SAVED_PAYMENT_CREDIT_CARD . "%'"
            . " OR transactionAfterOrder.PAYMENTTYPEID LIKE 's-"
            . SavedPaymentLoadService::SAVED_PAYMENT_SEPA_DIRECT_DEBIT . "%'";

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentLoadFilterService::getPaymentTypeIdLikeExpression
     */
    public function testGetPaymentTypeIdLikeExpressionValidMethodSpecific()
    {
        $method = SavedPaymentLoadService::SAVED_PAYMENT_PAYPAL;

        $this->savedPaymentMethodValidator
            ->method('validate')
            ->with($method)
            ->willReturn(true);

        $result = $this->filterService->getPaymentTypeIdLikeExpression($method);

        $expected = "transactionAfterOrder.PAYMENTTYPEID LIKE 's-{$method}%'";

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentLoadFilterService::getPaymentTypeIdLikeExpression
     */
    public function testGetPaymentTypeIdLikeExpressionInvalidMethod()
    {
        $invalidMethod = 'invalid_method';

        $this->savedPaymentMethodValidator
            ->method('validate')
            ->with($invalidMethod)
            ->willReturn(false);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Invalid savedPaymentMethod SavedPaymentService::getLastSavedPaymentTransaction: $invalidMethod"
        );

        $this->filterService->getPaymentTypeIdLikeExpression($invalidMethod);
    }
}
