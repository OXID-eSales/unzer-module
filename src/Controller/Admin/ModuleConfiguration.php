<?php

namespace OxidSolutionCatalysts\Unzer\Controller\Admin;

use GuzzleHttp\Exception\GuzzleException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Core\Exception\FileException;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;
use OxidSolutionCatalysts\Unzer\Exception\UnzerException;
use OxidSolutionCatalysts\Unzer\Module;
use OxidSolutionCatalysts\Unzer\Service\ApiClient;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use Throwable;
use UnzerSDK\Exceptions\UnzerApiException;
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
                $registeredWebhookUrl = $moduleSettings->getRegisteredWebhook();
                $registeredWebhookId = $moduleSettings->getRegisteredWebhookId();
                $proposedWebhookUrl = $this->getProposedWebhookForActualShop();

                if ($pubKey && $privKey) {
                    /** @var Unzer $unzer */
                    $unzer = $this->getServiceFromContainer(UnzerSDKLoader::class)->getUnzerSDK();
                    if (
                        $webhooks = $unzer->fetchAllWebhooks()
                    ) {
                        $webhookUrl = '';
                        $webhookId = '';
                        foreach ($webhooks as $webhook) {
                            if (
                                $webhook->getId() == $registeredWebhookId ||
                                $webhook->getUrl() == $proposedWebhookUrl
                            ) {
                                $webhookUrl = $webhook->getUrl();
                                $webhookId = $webhook->getId();
                                break;
                            }
                        }
                        if ($webhookUrl && $webhookId) {
                            $this->saveWebhookOption($webhookUrl, $webhookId);
                            $registeredWebhookUrl = $webhookUrl;
                        } else {
                            $this->saveWebhookOption('', '');
                            $registeredWebhookUrl = '';
                        }
                    }
                    $this->_aViewData["registeredwebhook"] = $registeredWebhookUrl;
                    $this->_aViewData["showWebhookButtons"] = true;
                }

                if ($capabilities = $moduleSettings->getApplePayMerchantCapabilities()) {
                    $this->_aViewData['applePayMC'] = $capabilities;
                }

                if ($networks = $moduleSettings->getApplePayNetworks()) {
                    $this->_aViewData['applePayNetworks'] = $networks;
                }

                if ($cert = $moduleSettings->getApplePayMerchantCert()) {
                    $this->_aViewData['applePayMerchantCert'] = $cert;
                }

                if ($key = $moduleSettings->getApplePayMerchantCertKey()) {
                    $this->_aViewData['applePayMerchantCertKey'] = $key;
                }
            } catch (Throwable $loggerException) {
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
     * @throws UnzerApiException
     */
    public function deleteWebhook(): void
    {
        /** @var Unzer $unzer */
        try {
            $unzer = $this->getServiceFromContainer(UnzerSDKLoader::class)->getUnzerSDK();
            $registeredWebhookId = $this->getServiceFromContainer(ModuleSettings::class)
                ->getRegisteredWebhookId();

            if ($webhooks = $unzer->fetchAllWebhooks()) {
                foreach ($webhooks as $webhook) {
                    if ($webhook->getId() == $registeredWebhookId) {
                        $unzer->deleteWebhook($webhook);
                        $this->saveWebhookOption('', '');
                    }
                }
            }
        } catch (Throwable $loggerException) {
            Registry::getUtilsView()->addErrorToDisplay(
                $this->translator->translateCode(
                    (string)$loggerException->getCode(),
                    $loggerException->getMessage()
                )
            );
        }
    }

    protected function getProposedWebhookForActualShop(): string
    {
        return Registry::getConfig()->getSslShopUrl()
            . 'index.php?cl=unzer_dispatcher&fnc=updatePaymentTransStatus';
    }

    /**
     * @throws UnzerApiException
     */
    public function registerWebhook(): void
    {
        try {
            /** @var Unzer $unzer */
            $unzer = $this->getServiceFromContainer(UnzerSDKLoader::class)->getUnzerSDK();
            $url = $this->getProposedWebhookForActualShop();

            $result = $unzer->createWebhook($url, "payment");
            $this->saveWebhookOption($url, $result->getId());
        } catch (Throwable $loggerException) {
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

    /**
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function transferApplePayPaymentProcessingData(): void
    {
        $key = Registry::getRequest()->getRequestEscapedParameter('applePayPaymentProcessingCertKey');
        $cert = Registry::getRequest()->getRequestEscapedParameter('applePayPaymentProcessingCert');
        $errorMessage = null;

        if (!$key || !$cert) {
            $errorMessage = 'OSCUNZER_ERROR_TRANSMITTING_APPLEPAY_PAYMENT_SET_CERT';
        }

        if (is_null($errorMessage)) {
            $apiClient = $this->getServiceFromContainer(ApiClient::class);
            $response = $apiClient->uploadApplePayPaymentKey($key);
            if ($response->getStatusCode() !== 200) {
                $errorMessage = 'OSCUNZER_ERROR_TRANSMITTING_APPLEPAY_PAYMENT_SET_KEY';
            }
        }

        if (is_null($errorMessage)) {
            $response = $apiClient->uploadApplePayPaymentCertificate($cert);
            if ($response->getStatusCode() !== 200) {
                $errorMessage = 'OSCUNZER_ERROR_TRANSMITTING_APPLEPAY_PAYMENT_SET_CERT';
            }
        }

        if ($errorMessage) {
            Registry::getUtilsView()->addErrorToDisplay(
                oxNew(
                    UnzerException::class,
                    Registry::getLang()->translateString(
                        $errorMessage
                    )
                )
            );
        }
    }

    /**
     * @throws GuzzleException
     */
    public function getApplePayPaymentProcessingKeyExists(): bool
    {
        $keyExists = false;
        try {
            $keyExists = $this->getServiceFromContainer(ApiClient::class)->requestApplePayPaymentCert()->getStatusCode() === 200;
        } catch (GuzzleException $guzzleException) {
            Registry::getUtilsView()->addErrorToDisplay(
                oxNew(
                    UnzerException::class,
                    Registry::getLang()->translateString(
                        'OSCUNZER_ERROR_TRANSMITTING_APPLEPAY_PAYMENT_GET_KEY'
                    )
                )
            );
        }
        return $keyExists;
    }

    /**
     * @throws GuzzleException
     */
    public function getApplePayPaymentProcessingCertExists(): bool
    {
        $certExists = false;
        try {
            $certExists = $this->getServiceFromContainer(ApiClient::class)->requestApplePayPaymentKey()->getStatusCode() === 200;
        } catch (GuzzleException $guzzleException) {
            Registry::getUtilsView()->addErrorToDisplay(
                oxNew(
                    UnzerException::class,
                    Registry::getLang()->translateString(
                        'OSCUNZER_ERROR_TRANSMITTING_APPLEPAY_PAYMENT_GET_CERT'
                    )
                )
            );
        }
        return $certExists;
    }

    /**
     * @return void
     * @throws FileException
     */
    public function saveConfVars()
    {
        parent::saveConfVars();

        $request = Registry::getRequest();
        $moduleSettings = $this->getServiceFromContainer(ModuleSettings::class);

        if ($requestApplePayMC = $request->getRequestEscapedParameter('applePayMC')) {
            $moduleSettings->saveApplePayMerchantCapabilities($requestApplePayMC);
        }
        if ($requestApplePayNetworks = $request->getRequestEscapedParameter('applePayNetworks')) {
            $moduleSettings->saveApplePayNetworks($requestApplePayNetworks);
        }
        if ($requestApplePayMerchantCert = $request->getRequestEscapedParameter('applePayMerchantCert')) {
            file_put_contents(
                $moduleSettings->getApplePayMerchantCertFilePath(),
                $requestApplePayMerchantCert
            );
        }
        if ($requestApplePayMerchantCertKey = $request->getRequestEscapedParameter('applePayMerchantCertKey')) {
            file_put_contents(
                $moduleSettings->getApplePayMerchantCertKeyFilePath(),
                $requestApplePayMerchantCertKey
            );
        }
    }
}
