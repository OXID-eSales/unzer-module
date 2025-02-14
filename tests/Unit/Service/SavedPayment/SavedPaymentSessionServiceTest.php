<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service\SavedPayment;

use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentSessionService;
use OxidEsales\Eshop\Core\Session;
use OxidSolutionCatalysts\Unzer\Service\RequestService;
use PHPUnit\Framework\TestCase;

class SavedPaymentSessionServiceTest extends TestCase
{
    private $sessionMock;
    private $requestServiceMock;
    private $savedPaymentSessionService;

    protected function setUp(): void
    {
        $this->sessionMock = $this->createMock(Session::class);
        $this->requestServiceMock = $this->createMock(RequestService::class);

        $this->savedPaymentSessionService = new SavedPaymentSessionService(
            $this->sessionMock,
            $this->requestServiceMock
        );
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentSessionService::isSavedPayment
     */
    public function testIsSavedPaymentReturnsTrueWhenSessionVariableIsTrue()
    {
        $sessionVariableName = SavedPaymentSessionService::class . '_userClickedSavePaymentCheckbox';
        $this->sessionMock->method('getVariable')
            ->with($sessionVariableName)
            ->willReturn(true);

        $result = $this->savedPaymentSessionService->isSavedPayment();

        $this->assertTrue($result);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentSessionService::isSavedPayment
     */
    public function testIsSavedPaymentReturnsFalseWhenSessionVariableIsFalse()
    {
        $sessionVariableName = SavedPaymentSessionService::class . '_userClickedSavePaymentCheckbox';
        $this->sessionMock->method('getVariable')
            ->with($sessionVariableName)
            ->willReturn(false);

        $result = $this->savedPaymentSessionService->isSavedPayment();

        $this->assertFalse($result);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentSessionService::setSavedPayment
     */
    public function testSetSavedPaymentSetsSessionVariableCorrectly()
    {
        $sessionVariableName = SavedPaymentSessionService::class . '_userClickedSavePaymentCheckbox';
        $this->requestServiceMock->method('isSavePaymentSelectedByUserInRequest')
            ->willReturn(true);

        $this->sessionMock->expects($this->once())
            ->method('setVariable')
            ->with($sessionVariableName, true);

        $this->savedPaymentSessionService->setSavedPayment();
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentSessionService::unsetSavedPayment
     */
    public function testUnsetSavedPaymentDeletesSessionVariableCorrectly()
    {
        $sessionVariableName = SavedPaymentSessionService::class . '_userClickedSavePaymentCheckbox';

        $this->sessionMock->expects($this->once())
            ->method('deleteVariable')
            ->with($sessionVariableName);

        $this->savedPaymentSessionService->unsetSavedPayment();
    }
}
