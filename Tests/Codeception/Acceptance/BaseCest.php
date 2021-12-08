<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

abstract class BaseCest
{
    public function _before(AcceptanceTester $I): void
    {
    }

    public function _after(AcceptanceTester $I): void
    {
    }
}
