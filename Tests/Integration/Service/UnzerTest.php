<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Service;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidSolutionCatalysts\Unzer\Service\Unzer;
use PHPUnit\Framework\TestCase;
use UnzerSDK\Resources\TransactionTypes\Charge;

class UnzerTest extends TestCase
{
    public function testGetBankDataFromCharge()
    {
        $stubData = [
            'getAmount' => 123456.5,
            'getHolder' => 'specialHolder',
            'getIban' => 'specialIban',
            'getBic' => 'specialBic',
            'getDescriptor' => 'specialDescriptor',
        ];
        $chargeStub = $this->createConfiguredMock(Charge::class, $stubData);

        $container = ContainerFactory::getInstance()->getContainer();
        /** @var Unzer $unzerService */
        $unzerService = $container->get(Unzer::class);

        $result = $unzerService->getBankDataFromCharge($chargeStub);

        $this->assertStringContainsString("123.456,50 €", $result);
        unset($stubData['getAmount']);
        foreach ($stubData as $oneValue) {
            $this->assertStringContainsString($oneValue, $result);
        }
    }
}
