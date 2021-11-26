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

namespace OxidSolutionCatalysts\Unzer\Model;

use Exception;
use UnzerSDK\Interfaces\DebugHandlerInterface;

class UnzerLogger implements DebugHandlerInterface
{
    private const LOG_TYPE_APPEND_TO_FILE = 3;

    /**
     * {@inheritDoc}
     *
     * ATTENTION: Please make sure the destination file is writable.
     * @throws Exception
     */
    public function log(string $message): void
    {
        /** @noinspection ForgottenDebugOutputInspection */
        error_log($message . "\n", self::LOG_TYPE_APPEND_TO_FILE, $this->getUnzerLoggingPath());
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getUnzerLogFolder(): string
    {
        $logfolder = getShopBasePath() . 'log/unzer/';
        if (!is_dir($logfolder) && !mkdir($logfolder)) {
            throw new Exception(sprintf('Directory "%s" was not created', $logfolder));
        }

        return $logfolder;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getUnzerLogFile(): string
    {
        return "unzer_" . date("Y-m-d") . ".log";
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getUnzerLoggingPath(): string
    {
        return $this->getUnzerLogFolder() . $this->getUnzerLogFile();
    }

}
