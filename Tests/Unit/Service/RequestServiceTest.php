<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;

use OxidSolutionCatalysts\Unzer\Service\RequestService;
use OxidEsales\Eshop\Core\Request;
use PHPUnit\Framework\TestCase;

class RequestServiceTest extends TestCase
{
    private $oxidCoreRequestServiceMock;
    private $requestService;

    protected function setUp(): void
    {
        $this->oxidCoreRequestServiceMock = $this->createMock(Request::class);

        $this->requestService = new RequestService($this->oxidCoreRequestServiceMock);
    }

    public function testIsSavePaymentSelectedByUserInRequestReturnsTrueWhenParameterIsTrue()
    {
        $this->oxidCoreRequestServiceMock->method('getRequestParameter')
            ->with('oscunzersavepayment', false)
            ->willReturn(true);

        $result = $this->requestService->isSavePaymentSelectedByUserInRequest();

        $this->assertTrue($result);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\RequestService::isSavePaymentSelectedByUserInRequest
     */
    public function testIsSavePaymentSelectedByUserInRequestReturnsFalseWhenParameterIsFalse()
    {
        $this->oxidCoreRequestServiceMock->method('getRequestParameter')
            ->with('oscunzersavepayment', false)
            ->willReturn(false);

        $result = $this->requestService->isSavePaymentSelectedByUserInRequest();

        $this->assertFalse($result);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\RequestService::isSavePaymentSelectedByUserInRequest
     */
    public function testIsSavePaymentSelectedByUserInRequestReturnsDefaultValueWhenParameterNotSet()
    {
        $this->oxidCoreRequestServiceMock->method('getRequestParameter')
            ->with('oscunzersavepayment', false)
            ->willReturn(false);

        $result = $this->requestService->isSavePaymentSelectedByUserInRequest();

        $this->assertFalse($result);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\RequestService::isSavePaymentSelectedByUserInRequest
     */
    public function testGetUnzerBoolRequestParameterReturnsTrueWhenParameterIsTrue()
    {
        $parameterName = 'test_parameter';
        $this->oxidCoreRequestServiceMock->method('getRequestParameter')
            ->with($parameterName, false)
            ->willReturn(true);

        $result = $this->invokeMethod($this->requestService, 'getUnzerBoolRequestParameter', [$parameterName]);

        $this->assertTrue($result);
    }

    public function testGetUnzerBoolRequestParameterReturnsFalseWhenParameterIsFalse()
    {
        $parameterName = 'test_parameter';
        $this->oxidCoreRequestServiceMock->method('getRequestParameter')
            ->with($parameterName, false)
            ->willReturn(false);

        $result = $this->invokeMethod(
            $this->requestService,
            'getUnzerBoolRequestParameter',
            [$parameterName]
        );

        $this->assertFalse($result);
    }

    /**
     * Helper method to invoke a private or protected method
     */
    private function invokeMethod($object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
