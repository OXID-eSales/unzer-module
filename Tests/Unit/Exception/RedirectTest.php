<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Exception;

use OxidSolutionCatalysts\Unzer\Exception\Redirect;
use PHPUnit\Framework\TestCase;

class RedirectTest extends TestCase
{
    public function testIsThrowable(): void
    {
        $sut = new Redirect('x');
        $this->assertInstanceOf(\Throwable::class, $sut);
    }

    public function testBasicGetters(): void
    {
        $destination = 'redirectDirection';
        $sut = new Redirect($destination);

        $this->assertSame($destination, $sut->getDestination());
    }
}
