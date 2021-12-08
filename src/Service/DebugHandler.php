<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use Monolog\Logger;

class DebugHandler implements \UnzerSDK\Interfaces\DebugHandlerInterface
{
    /** @var Logger */
    protected $logger;

    public function __construct(Logger $moduleLogger)
    {
        $this->logger = $moduleLogger;
    }

    public function log(string $message): void
    {
        $this->logger->info($message);
    }
}
