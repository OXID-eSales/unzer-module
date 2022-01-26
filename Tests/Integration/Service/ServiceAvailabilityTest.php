<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Service;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidSolutionCatalysts\Unzer\Service\DebugHandler;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use OxidSolutionCatalysts\Unzer\Service\Payment;
use OxidSolutionCatalysts\Unzer\Service\PaymentValidator;
use OxidSolutionCatalysts\Unzer\Service\Transaction;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Service\Unzer;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use PHPUnit\Framework\TestCase;

class ServiceAvailabilityTest extends TestCase
{
    /**
     * @dataProvider serviceAvailabilityDataProvider
     */
    public function testLoggerAvailable($service): void
    {
        $container = ContainerFactory::getInstance()->getContainer();
        $this->assertInstanceOf($service, $container->get($service));
    }

    public function serviceAvailabilityDataProvider(): array
    {
        return [
            [DebugHandler::class],
            [ModuleSettings::class],
            [UnzerSDKLoader::class],
            [Translator::class],
            [Transaction::class],
            [Unzer::class],
            [PaymentValidator::class],
            [Payment::class],
        ];
    }
}
