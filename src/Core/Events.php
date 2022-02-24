<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Core;

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
     * @throws Exception
     */
    public static function onActivate()
    {
        // execute module migrations
        self::executeModuleMigrations();

        //add static contents and payment methods
        //NOTE: this assumes the module's servies.yaml is already in place at the time this method is called
        self::addStaticContents();
    }


    /**
     * Execute action on deactivate event
     *
     * @return void
     * @throws Exception
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
        $migrations->execute('migrations:migrate', 'osc-unzer');
    }

    /**
     * Execute necessary module migrations on activate event
     *
     * @return void
     */
    private static function addStaticContents(): void
    {
        /** @var StaticContent $service */
        $service = ContainerFactory::getInstance()
            ->getContainer()
            ->get(StaticContent::class);

        $service->ensureStaticContents();
        $service->ensureUnzerPaymentMethods();
        $service->createRdfa();
    }
}
