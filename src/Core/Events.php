<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Core;

use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use OxidEsales\DoctrineMigrationWrapper\MigrationsBuilder;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use OxidSolutionCatalysts\Unzer\Service\StaticContent;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;

/**
 * Class defines what module does on Shop events.
 */
class Events
{
    use ServiceContainer;

    /**
     * Execute action on activate event
     *
     * @return void
     * @throws \Exception
     */
    public static function onActivate()
    {
        // execute module migrations
        self::executeModuleMigrations();

        //add static contents and payment methods
        self::addStaticContents();
    }

    /**
     * Execute action on deactivate event
     *
     * @return void
     * @throws \Exception
     */
    public static function onDeactivate()
    {
    }

    /**
     * Execute necessary module migrations on activate event
     *
     * @return void
     */
    private static function executeModuleMigrations(): void
    {
        $migrations = (new MigrationsBuilder())->build();

        $output = new BufferedOutput();
        $migrations->setOutput($output);
        $neeedsUpdate = $migrations->execute('migrations:up-to-date', 'osc-unzer');

        if ($neeedsUpdate) {
            $migrations->execute('migrations:migrate', 'osc-unzer');
        }
    }

    /**
     * Execute necessary module migrations on activate event
     *
     * @return void
     */
    private static function addStaticContents(): void
    {
        $service = self::getStaticContentService();

        $service->ensureStaticContents();
        $service->ensureUnzerPaymentMethods();
        $service->createRdfa();
    }

    private static function getStaticContentService(): StaticContent
    {
        /*
        Normally I would fetch the StaticContents service like this:

        $service = ContainerFactory::getInstance()
            ->getContainer()
            ->get(StaticContent::class);

        But the services are not ready when the onActivate method is triggered.
        That's why I build the containers by hand as an exception.:
        */

        /** @var ContainerInterface $container */
        $container = ContainerFactory::getInstance()
            ->getContainer();
        /** @var QueryBuilderFactoryInterface $queryBuilderFactory */
        $queryBuilderFactory = $container->get(QueryBuilderFactoryInterface::class);
        /** @var ContextInterface $context */
        $context = $container->get(ContextInterface::class);

        return new StaticContent(
            $queryBuilderFactory,
            $context
        );
    }
}
