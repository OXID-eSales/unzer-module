<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidSolutionCatalysts\Unzer\Core\UnzerDefinitions;
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
     * @var Session
     */
    private $session;

    /**
     * @param ModuleSettings $moduleSettings
     * @param DebugHandler $debugHandler
     */
    public function __construct(
        ModuleSettings $moduleSettings,
        DebugHandler $debugHandler,
        Session $session
    ) {
        $this->moduleSettings = $moduleSettings;
        $this->debugHandler = $debugHandler;
        $this->session = $session;
    }

    /**
     * @return Unzer
     */
    public function getUnzerSDK(): Unzer
    {
        $key = $this->moduleSettings->getShopPrivateKey();
        if ($this->session->getBasket()->getPaymentId() === UnzerDefinitions::INVOICE_UNZER_PAYMENT_ID) {
            $request = Registry::getRequest();
            $customerType = $request->getRequestParameter('unzer_customer_type');

            $key = $this->moduleSettings->getShopPrivateKeyInvoice($customerType);
        }

        $sdk = oxNew(Unzer::class, $key);

        if ($this->moduleSettings->isDebugMode()) {
            $sdk->setDebugMode(true)->setDebugHandler($this->debugHandler);
        }

        return $sdk;
    }
}
