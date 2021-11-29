<?php

namespace OxidSolutionCatalysts\Unzer\Utility;

use Monolog\Logger;

class DebugHandler implements \UnzerSDK\Interfaces\DebugHandlerInterface
{
    /** @var Logger */
    protected $logger;

    public function __construct(Logger $moduleLogger)
    {
        $this->logger = $moduleLogger;
    }

    public function log(string $message)
    {
        $this->logger->info($message);
    }
}