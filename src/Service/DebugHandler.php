<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Service;

use Monolog\Logger;
use UnzerSDK\Interfaces\DebugHandlerInterface;

class DebugHandler implements DebugHandlerInterface
{
    /** @var Logger */
    protected Logger $logger;

    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * @param Logger $moduleLogger
     */
    public function __construct(Logger $moduleLogger)
    {
        $this->logger = $moduleLogger;
    }

    /**
     * @param string $message
     */
    public function log(string $message): void
    {
        $this->getLogger()->info($message);
    }
}
