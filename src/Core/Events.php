<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Core;

use Exception;
use OxidEsales\DoctrineMigrationWrapper\MigrationsBuilder;
use OxidEsales\Eshop\Core\DbMetaDataHandler;
use OxidEsales\Eshop\Core\Registry;

/**
 * Class defines what module does on Shop events.
 */
class Events
{
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

        // clear tmp
        self::clearTmp();

        // update views
        $oDbMeta = oxNew(DbMetaDataHandler::class);
        $oDbMeta->updateViews();
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
     * clearTmp
     *
     * Clears the tmp folder
     *
     * @return void
     */
    private static function clearTmp(): void
    {
        $oConf = Registry::getConfig();
        $sTmpDir = realpath($oConf->getConfigParam('sCompileDir'));

        $aFiles = glob($sTmpDir . '/*{.php,.txt,.inc}', GLOB_BRACE);
        $aFiles = array_merge($aFiles, glob($sTmpDir . '/smarty/*{.inc,.php}', GLOB_BRACE));

        if (count($aFiles) > 0) {
            foreach ($aFiles as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }
}
