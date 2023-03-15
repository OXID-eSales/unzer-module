<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Core\Registry;
use UnzerSDK\Adapter\ApplepayAdapter;
use UnzerSDK\Resources\ExternalResources\ApplepaySession;

class ApplePaySessionHandler
{
    private ApplepaySession $session;
    private ApplepayAdapter $adapter;
    private ModuleSettings $moduleSettings;

    /**
     * @param ModuleSettings $moduleSettings
     */
    public function __construct(ModuleSettings $moduleSettings)
    {
        $this->moduleSettings = $moduleSettings;
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
        /** @var string $applePatLabel */
        $applePatLabel = $this->moduleSettings->getApplePayLabel();
        $this->session = new ApplepaySession(
            $this->moduleSettings->getApplePayMerchantIdentifier(),
            $applePatLabel,
            $domainName
        );
        $this->adapter = new ApplepayAdapter();
        $this->adapter->init(
            $this->moduleSettings->getApplePayMerchantCertFilePath(),
            $this->moduleSettings->getApplePayMerchantCertKeyFilePath()
        );
    }

    /**
     * @param string $validationUrl
     * @return array|null
     */
    public function validateMerchant(string $validationUrl): ?array
    {
        try {
            /** @var string $validApplePayMerch */
            $validApplePayMerch = $this->adapter->validateApplePayMerchant($validationUrl, $this->session);
            /** @var array $jsonDecoded */
            $jsonDecoded = json_decode(
                $validApplePayMerch,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
            return $jsonDecoded;
        } catch (\Throwable $e) {
            Registry::getLogger()->error($e->getMessage());
            return null;
        }
    }
}
