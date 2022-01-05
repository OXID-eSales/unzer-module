<?php

namespace OxidSolutionCatalysts\Unzer\Traits;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;

trait ServiceContainer
{
    /**
     * @template T
     * @psalm-param class-string<T> $serviceName
     * @return T
     */
    protected function getServiceFromContainer(string $serviceName)
    {
        return ContainerFactory::getInstance()
            ->getContainer()
            ->get($serviceName);
    }
}
