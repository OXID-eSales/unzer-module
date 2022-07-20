<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Core\Registry;
use UnzerSDK\Adapter\ApplepayAdapter;
use UnzerSDK\Resources\ExternalResources\ApplepaySession;

class ApplePaySessionHandler
{
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
        $domainName = rtrim(
            str_replace(
                ['http://', 'https://'],
                '',
                Registry::getConfig()->getSslShopUrl()
            ),
            '/'
        );
        $this->session = new ApplepaySession(
            (string) $this->moduleSettingsService->getApplePayMerchantIdentifier(),
            (string) $this->moduleSettingsService->getApplePayLabel(),
            $domainName
        );
        $this->adapter = new ApplepayAdapter();
        $this->adapter->init(
            $this->moduleSettingsService->getApplePayMerchantCertFilePath(),
            $this->moduleSettingsService->getApplePayMerchantCertKeyFilePath()
        );
    }

    /**
     * @param string $validationUrl
     * @return array|null
     */
    public function validateMerchant(string $validationUrl): ?array
    {
        try {
            return json_decode(
                $this->adapter->validateApplePayMerchant(
                    $validationUrl,
                    $this->session
                ),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\Throwable $e) {
            Registry::getLogger()->error($e->getMessage());
            return null;
        }
    }
}
