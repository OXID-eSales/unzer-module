<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Core\Registry;
use UnzerSDK\Adapter\ApplepayAdapter;
use UnzerSDK\Resources\ExternalResources\ApplepaySession;

class ApplePayHandler
{
    const MERCHANT_IDENTIFIER = 'merchant.io.unzer.merchantconnectivity';
    const IDENTIFIER = 'PHP-SDK Example';

    private ApplepaySession $session;
    private ApplepayAdapter $adapter;
    private ModuleSettings $moduleSettingsService;

    public function __construct(ModuleSettings $moduleSettings)
    {
        $this->moduleSettingsService = $moduleSettings;
        $this->init();
    }

    /**
     * @return void
     */
    private function init(): void
    {
        $this->session = new ApplepaySession(self::MERCHANT_IDENTIFIER, self::IDENTIFIER, Registry::getConfig()->getSslShopUrl());
        $this->adapter = new ApplepayAdapter();
        $this->adapter->init($this->moduleSettingsService->getApplePayMerchantCert(), $this->moduleSettingsService->getApplePayMerchantCertKey());
    }
}