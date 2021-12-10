<?php

/**
 * This file is part of OXID eSales Unzer module.
 *
 * OXID eSales Unzer module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales Unzer module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales Unzer module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright 2003-2021 OXID eSales AG
 * @link      http://www.oxid-esales.com
 * @author    OXID Solution Catalysts
 */

namespace OxidSolutionCatalysts\Unzer\Core;

use Exception;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\DoctrineMigrationWrapper\MigrationsBuilder;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\DbMetaDataHandler;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setup\Bridge\ModuleActivationBridgeInterface;
use OxidEsales\Facts\Facts;
use Psr\Container\ContainerInterface;

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
     * ContainerFactory, ContainerInterface
     *
     * @return ContainerInterface
     * @internal
     */
    protected static function getContainer(): ContainerInterface
    {
        return ContainerFactory::getInstance()->getContainer();
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
