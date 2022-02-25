<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Core\Registry;
use UnzerSDK\Adapter\ApplepayAdapter;
use UnzerSDK\Exceptions\ApplepayMerchantValidationException;
use UnzerSDK\Resources\ExternalResources\ApplepaySession;

class ApplePaySessionHandler
{
    private const IDENTIFIER = 'OXID Unzer';

    private ApplepaySession $session;
    private ApplepayAdapter $adapter;
    private ModuleSettings $moduleSettingsService;

    /**
     * @param ModuleSettings $moduleSettings
     */
    public function __construct(ModuleSettings $moduleSettings)
    {
        $this->moduleSettingsService = $moduleSettings;
        $this->initialize();
    }

    /**
     * @return void
     */
    private function initialize(): void
    {
        $this->session = new ApplepaySession($this->moduleSettingsService->getApplePayMerchantIdentifier(), self::IDENTIFIER, Registry::getConfig()->getSslShopUrl());
        $this->adapter = new ApplepayAdapter();
        $this->adapter->init($this->moduleSettingsService->getApplePayMerchantCertFilePath(), $this->moduleSettingsService->getApplePayMerchantCertKeyFilePath());
    }

    /**
     * @param string $validationUrl
     * @return string|null
     */
    public function validateMerchant(string $validationUrl): ?string
    {
        try {
            return $this->adapter->validateApplePayMerchant($validationUrl, $this->session);
        } catch (ApplepayMerchantValidationException $e) {
            Registry::getLogger()->error($e->getMessage());
            return false;
        }
    }
}