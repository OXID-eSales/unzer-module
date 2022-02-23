<?php

namespace OxidSolutionCatalysts\Unzer\Controller\Admin;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;
use OxidSolutionCatalysts\Unzer\Module;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use UnzerSDK\Unzer;

/**
 * Order class wrapper for Unzer module
 */
class ModuleConfiguration extends ModuleConfiguration_parent
{
    use ServiceContainer;

    protected $translator;
    protected string $_sModuleId; // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->translator = oxNew(Translator::class, Registry::getLang());
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        parent::render();

        if ($this->_sModuleId == Module::MODULE_ID) {
            try {
                $moduleSettings = $this->getServiceFromContainer(ModuleSettings::class);
                $pubKey = $moduleSettings->getShopPublicKey();
                $privKey = $moduleSettings->getShopPrivateKey();

                if ($pubKey && $privKey) {
                    $this->_aViewData["shobWebhookButtons"] = true;
                    /** @var Unzer $unzer */
                    $unzer = $this->getServiceFromContainer(UnzerSDKLoader::class)->getUnzerSDK();
                    if ($aWebhooks = $unzer->fetchAllWebhooks()) {
                        $this->_aViewData["registeredwebhook"] = $aWebhooks[0]->getUrl();
                    }
                }

                if ($capabilities = $moduleSettings->getApplePayMerchantCapabilities()) {
                    $this->_aViewData['applePayMC'] = $capabilities;
                }

                if ($networks = $moduleSettings->getApplePayNetworks()) {
                    $this->_aViewData['applePayNetworks'] = $networks;
                }
            } catch (\Throwable $loggerException) {
                Registry::getUtilsView()->addErrorToDisplay(
                    $this->translator->translateCode(
                        (string)$loggerException->getCode(),
                        $loggerException->getMessage()
                    )
                );
            }
        }
        return 'module_config.tpl';
    }

    /**
     * @throws \UnzerSDK\Exceptions\UnzerApiException
     */
    public function deleteWebhook(): void
    {
        /** @var Unzer $unzer */
        try {
            $unzer = $this->getServiceFromContainer(UnzerSDKLoader::class)->getUnzerSDK();
            $unzer->deleteAllWebhooks();
            $moduleSettingBridge = ContainerFactory::getInstance()
                ->getContainer()
                ->get(ModuleSettingBridgeInterface::class);
            $moduleSettingBridge->save('registeredWebhook', '', Module::MODULE_ID);
        } catch (\Throwable $loggerException) {
            Registry::getUtilsView()->addErrorToDisplay(
                $this->translator->translateCode(
                    (string)$loggerException->getCode(),
                    $loggerException->getMessage()
                )
            );
        }
    }

    /**
     * @throws \UnzerSDK\Exceptions\UnzerApiException
     */
    public function registerWebhook(): void
    {
        try {
            /** @var Unzer $unzer */
            $unzer = $this->getServiceFromContainer(UnzerSDKLoader::class)->getUnzerSDK();
            $url = Registry::getConfig()->getSslShopUrl()
                . 'index.php?cl=unzer_dispatcher&fnc=updatePaymentTransStatus';

            $unzer->createWebhook($url, "payment");
            $moduleSettingBridge = ContainerFactory::getInstance()
                ->getContainer()
                ->get(ModuleSettingBridgeInterface::class);
            $moduleSettingBridge->save('registeredWebhook', $url, Module::MODULE_ID);
        } catch (\Throwable $loggerException) {
            Registry::getUtilsView()->addErrorToDisplay(
                $this->translator->translateCode(
                    (string)$loggerException->getCode(),
                    $loggerException->getMessage()
                )
            );
        }
    }

    public function transferApplePayCert(): void
    {

    }

    public function transferApplePayPrivateKey(): void
    {
        
    }

    public function saveConfVars()
    {
        parent::saveConfVars();

        $request = Registry::getRequest();
        $request->getRequestEscapedParameter('applePayMC');
        $moduleSettings = $this->getServiceFromContainer(ModuleSettings::class);

        $moduleSettings->saveApplePayMerchantCapabilities($request->getRequestEscapedParameter('applePayMC'));
        $moduleSettings->saveApplePayNetworks($request->getRequestEscapedParameter('applePayNetworks'));
    }
}
