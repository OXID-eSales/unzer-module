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

    protected $_translator;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->_translator = oxNew(Translator::class, Registry::getLang());
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        parent::render();

        if ($this->_sModuleId == Module::MODULE_ID) {
            try {

                $pubKey = $this->getServiceFromContainer(ModuleSettings::class)->getShopPublicKey();
                $privKey = $this->getServiceFromContainer(ModuleSettings::class)->getShopPrivateKey();

                if ($pubKey && $privKey) {
                    $this->_aViewData["shobWebhookButtons"] = true;
                    /** @var Unzer $unzer */
                    $unzer = $this->getServiceFromContainer(UnzerSDKLoader::class)->getUnzerSDK();
                    if ($aWebhooks = $unzer->fetchAllWebhooks()) {
                        $this->_aViewData["registeredwebhook"] = $aWebhooks[0]->getUrl();
                    }
                }

            } catch (\Throwable $loggerException) {
                Registry::getUtilsView()->addErrorToDisplay($this->_translator->translateCode((string)$loggerException->getCode(), $loggerException->getClientMessage()));
            }
        }
        return 'module_config.tpl';
    }

    /**
     * @throws \UnzerSDK\Exceptions\UnzerApiException
     */
    public function deleteWebhook()
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
            Registry::getUtilsView()->addErrorToDisplay($this->_translator->translateCode((string)$loggerException->getCode(), $loggerException->getClientMessage()));
        }
    }

    /**
     * @throws \UnzerSDK\Exceptions\UnzerApiException
     */
    public function registerWebhook()
    {
        try {
            /** @var Unzer $unzer */
            $unzer = $this->getServiceFromContainer(UnzerSDKLoader::class)->getUnzerSDK();
            //$url = Registry::getConfig()->getSslShopUrl() . 'index.php?cl=unzer_dispatcher&fnc=updatePaymentTransStatus';
            $url = 'https://www.dixeno.de/index.php?cl=unzer_dispatcher&fnc=updatePaymentTransStatus';

            if ($unzer->createWebhook($url, "payment")) {
                $moduleSettingBridge = ContainerFactory::getInstance()
                    ->getContainer()
                    ->get(ModuleSettingBridgeInterface::class);
                $moduleSettingBridge->save('registeredWebhook', $url, Module::MODULE_ID);
            }
        } catch (\Throwable $loggerException) {
            Registry::getUtilsView()->addErrorToDisplay($this->_translator->translateCode((string)$loggerException->getCode(), $loggerException->getClientMessage()));
        }
    }
}
