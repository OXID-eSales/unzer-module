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
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;

class UnzerLogger extends Logger
{
    /**
     * The loglevel that is configured
     *
     * @var int
     */
    protected int $logLevel;

    /**
     * @var string
     */
    private string $logfile;

    /**
     *
     */
    public function __construct()
    {
        $this->setLoggingFile();

        $this->setLogLevel();
        $handler = new RotatingFileHandler($this->logfile, 14);

        $handler->pushProcessor(new UidProcessor());
        $handler->setFormatter(new LineFormatter());

        parent::__construct('Unzer', array($handler));
    }

    /**
     * @return int
     */
    public function setLogLevel(): int
    {
        /*
        * 0 = Debug
        * 1 = Warning
        * 2 = Error
        */
        switch (UnzerHelper::getConfigParam('UnzerLogLevel')) {
            case 0:
                $this->logLevel = Logger::DEBUG;
                break;
            case 1:
                $this->logLevel = Logger::WARNING;
                break;
            case 2:
                $this->logLevel = Logger::ERROR;
                break;
            default:
                $this->logLevel = Logger::DEBUG;
        }
        return $this->logLevel;
    }

    /**
     * @return int
     */
    public function getLogLevel(): int
    {
        return $this->logLevel;
    }

    /**
     * Adds a log record.
     *
     * @param int $level The logging level
     * @param string $message The log message
     * @param array $context The log context
     * @return bool Whether the record has been processed
     */
    public function addRecord($level, $message, array $context = array()): bool
    {
        $result = false;
        if ($level >= $this->logLevel) {
            $result = parent::addRecord($level, $message, $context);
        }

        return $result;
    }

    public function setLoggingFile()
    {
        $this->logfile = $this->getUnzerLogFolder();
        $this->logfile .= "unzer.log";
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
}
