<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Exception;

use OxidSolutionCatalysts\Unzer\Exception\MessageRedirect;
use PHPUnit\Framework\TestCase;

class MessageRedirectTest extends TestCase
{
    public function testIsThrowable(): void
    {
        $sut = new MessageRedirect('x', 'y');
        $this->assertInstanceOf(\Throwable::class, $sut);
    }

    public function testBasicGetters(): void
    {
        $destination = 'redirectDirection';
        $messageKey = 'messageKey';
        $sut = new MessageRedirect($destination, $messageKey);

        $this->assertSame($destination, $sut->getDestination());
        $this->assertSame($messageKey, $sut->getMessageKey());
        $this->assertSame('', $sut->getDefaultMessage());
    }

    public function testGetDefaultMessage(): void
    {
        $message = 'customMessage';
        $sut = new MessageRedirect('x', 'y', $message);

        $this->assertSame($message, $sut->getDefaultMessage());
    }
}
