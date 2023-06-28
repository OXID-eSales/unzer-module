<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Exception;

use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidSolutionCatalysts\Unzer\Exception\Redirect;

class RedirectTest extends IntegrationTestCase
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
