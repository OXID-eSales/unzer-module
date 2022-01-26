<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Service;

use UnzerSDK\Unzer;

class UnzerSDKLoader
{
    /**
     * @var ModuleSettings
     */
    private $moduleSettings;

    /**
     * @var DebugHandler
     */
    private $debugHandler;

    /**
     * @param ModuleSettings $moduleSettings
     * @param DebugHandler $debugHandler
     */
    public function __construct(
        ModuleSettings $moduleSettings,
        DebugHandler $debugHandler
    ) {
        $this->moduleSettings = $moduleSettings;
        $this->debugHandler = $debugHandler;
    }

    /**
     * @return Unzer
     */
    public function getUnzerSDK(): Unzer
    {
        $sdk = oxNew(Unzer::class, $this->moduleSettings->getShopPrivateKey());

        if ($this->moduleSettings->isDebugMode()) {
            $sdk->setDebugMode(true)->setDebugHandler($this->debugHandler);
        }

        return $sdk;
    }
}
