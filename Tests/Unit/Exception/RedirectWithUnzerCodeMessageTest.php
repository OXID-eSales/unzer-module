<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Exception;

use OxidSolutionCatalysts\Unzer\Exception\RedirectWithUnzerCodeMessage;
use PHPUnit\Framework\TestCase;

class RedirectWithUnzerCodeMessageTest extends TestCase
{
    public function testIsThrowable(): void
    {
        $sut = new RedirectWithUnzerCodeMessage('x', 'y');
        $this->assertInstanceOf(\Throwable::class, $sut);
    }

    public function testBasicGetters(): void
    {
        $destination = 'redirectDirection';
        $unzerCode = 'unzerErrorCode';
        $sut = new RedirectWithUnzerCodeMessage($destination, $unzerCode);

        $this->assertSame($destination, $sut->getDestination());
        $this->assertSame($unzerCode, $sut->getUnzerErrorCode());
        $this->assertSame('', $sut->getDefaultMessage());
    }

    public function testGetDefaultMessage(): void
    {
        $message = 'customMessage';
        $sut = new RedirectWithUnzerCodeMessage('x', 'y', $message);

        $this->assertSame($message, $sut->getDefaultMessage());
    }
}
