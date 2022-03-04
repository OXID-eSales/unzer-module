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
                $pubKey = $this->getServiceFromContainer(ModuleSettings::class)->getShopPublicKey();
                $privKey = $this->getServiceFromContainer(ModuleSettings::class)->getShopPrivateKey();
                $registeredWebhook = $this->getServiceFromContainer(ModuleSettings::class)->getRegisteredWebhook();
                $registeredWebhookId = $this->getServiceFromContainer(ModuleSettings::class)->getRegisteredWebhookId();

                if ($pubKey && $privKey) {
                    $this->_aViewData["shobWebhookButtons"] = true;
                    /** @var Unzer $unzer */
                    $unzer = $this->getServiceFromContainer(UnzerSDKLoader::class)->getUnzerSDK();

                    if ($webhooks = $unzer->fetchAllWebhooks()) {
                        $webhookUrl = '';
                        foreach ($webhooks as $webhook) {
                            if ($webhook->getId() == $registeredWebhookId) {
                                $webhookUrl = $webhook->getUrl();
                                break;
                            }
                        }
                        $this->_aViewData["registeredwebhook"] = $webhookUrl;
                    }
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
            $registeredWebhookId = $this->getServiceFromContainer(ModuleSettings::class)->getRegisteredWebhookId();

            if ($webhooks = $unzer->fetchAllWebhooks()) {
                foreach ($webhooks as $webhook) {
                    if ($webhook->getId() == $registeredWebhookId) {
                        $unzer->deleteWebhook($webhook);
                        $this->saveWebhookOption('', '');
                    }
                }
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

            $result = $unzer->createWebhook($url, "payment");
            $this->saveWebhookOption($url, $result->getId());
        } catch (\Throwable $loggerException) {
            Registry::getUtilsView()->addErrorToDisplay(
                $this->translator->translateCode(
                    (string)$loggerException->getCode(),
                    $loggerException->getMessage()
                )
            );
        }
    }

    protected function saveWebhookOption($url, $id): void
    {
        $moduleSettingBridge = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
        $moduleSettingBridge->save('registeredWebhook', $url, Module::MODULE_ID);
        $moduleSettingBridge->save('registeredWebhookId', $id, Module::MODULE_ID);
    }
}
