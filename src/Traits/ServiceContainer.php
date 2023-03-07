<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Traits;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;

trait ServiceContainer
{
    /**
     * @template T
     * @param class-string<T> $serviceName
     * @return T
     */
    protected function getServiceFromContainer(string $serviceName)
    {
        return ContainerFactory::getInstance()
            ->getContainer()
            ->get($serviceName);
    }
}
