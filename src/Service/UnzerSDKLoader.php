<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use UnzerSDK\Unzer;

class UnzerSDKLoader
{
    private $moduleSettings;

    private $debugHandler;

    public function __construct(
        ModuleSettings $moduleSettings,
        DebugHandler $debugHandler
    ) {
        $this->moduleSettings = $moduleSettings;
        $this->debugHandler = $debugHandler;
    }

    public function getUnzerSDK(): Unzer
    {
        $sdk = oxNew(Unzer::class, $this->moduleSettings->getShopPrivateKey());

        if ($this->moduleSettings->isDebugMode()) {
            $sdk->setDebugMode(true)->setDebugHandler($this->debugHandler);
        }

        return $sdk;
    }
}
