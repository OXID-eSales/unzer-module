<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

// This is acceptance bootstrap
use Codeception\Util\Autoload;

Autoload::addNamespace('OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance', __DIR__);
$helper = new \OxidEsales\Codeception\Module\FixturesHelper();
$helper->loadRuntimeFixtures(__DIR__ . '/../_data/fixtures.php');
