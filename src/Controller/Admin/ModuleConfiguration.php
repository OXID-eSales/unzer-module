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

    /** @var Translator $translator */
    protected $translator = null;
    /** @var ModuleSettings $moduleSettings */
    protected $moduleSettings = null;
    protected string $_sModuleId; // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->translator = $this->getServiceFromContainer(Translator::class);
        $this->moduleSettings = $this->getServiceFromContainer(ModuleSettings::class);
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        parent::render();

        if ($this->_sModuleId == Module::MODULE_ID) {
            try {
                $pubKey = $this->moduleSettings->getShopPublicKey();
                $privKey = $this->moduleSettings->getShopPrivateKey();
                $registeredWebhookUrl = $this->moduleSettings->getRegisteredWebhook();
                $registeredWebhookId = $this->moduleSettings->getRegisteredWebhookId();
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
                            // There is a webhook set at Unzer, but it is not yet saved in the shop.
                            // So save again
                            if (!$registeredWebhookUrl || !$registeredWebhookId) {
                                $this->saveWebhookOption($webhookUrl, $webhookId);
                            }
                            $registeredWebhookUrl = $webhookUrl;
                        } else {
                            $registeredWebhookUrl = '';
                        }
                    }
                    $this->_aViewData["registeredwebhook"] = $registeredWebhookUrl;
                    $this->_aViewData["showWebhookButtons"] = true;
                }

                if ($capabilities = $this->moduleSettings->getApplePayMerchantCapabilities()) {
                    $this->_aViewData['applePayMC'] = $capabilities;
                }

                if ($networks = $this->moduleSettings->getApplePayNetworks()) {
                    $this->_aViewData['applePayNetworks'] = $networks;
                }

                if ($cert = $this->moduleSettings->getApplePayMerchantCert()) {
                    $this->_aViewData['applePayMerchantCert'] = $cert;
                }

                if ($key = $this->moduleSettings->getApplePayMerchantCertKey()) {
                    $this->_aViewData['applePayMerchantCertKey'] = $key;
                }

                $this->_aViewData['systemMode'] = $this->moduleSettings->getSystemMode();
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
        try {
            /** @var Unzer $unzer */
            $unzer = $this->getServiceFromContainer(UnzerSDKLoader::class)->getUnzerSDK();
            $registeredWebhookId = $this->moduleSettings->getRegisteredWebhookId();

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
        $withXDebug = ($this->moduleSettings->isSandboxMode() && $this->moduleSettings->isDebugMode());
        return Registry::getConfig()->getSslShopUrl()
            . 'index.php?cl=unzer_dispatcher&fnc=updatePaymentTransStatus'
            . ($withXDebug ? '&XDEBUG_SESSION_START' : '');
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
            /** @var string $resultId */
            $resultId = $result->getId();
            $this->saveWebhookOption($url, $resultId);
        } catch (Throwable $loggerException) {
            Registry::getUtilsView()->addErrorToDisplay(
                $this->translator->translateCode(
                    (string)$loggerException->getCode(),
                    $loggerException->getMessage()
                )
            );
        }
    }

    /**
     * @param string $url
     * @param string $id
     * @return void
     */
    protected function saveWebhookOption(string $url, string $id): void
    {
        $this->moduleSettings->saveWebhook($url);
        $this->moduleSettings->saveWebhookId($id);
    }

    /**
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function transferApplePayPaymentProcessingData(): void
    {
        /** @var string $key */
        $key = Registry::getRequest()->getRequestEscapedParameter('applePayPaymentProcessingCertKey');
        /** @var string $cert */
        $cert = Registry::getRequest()->getRequestEscapedParameter('applePayPaymentProcessingCert');
        $errorMessage = null;

        if (!$key || !$cert) {
            $errorMessage = 'OSCUNZER_ERROR_TRANSMITTING_APPLEPAY_PAYMENT_SET_CERT';
        }

        $apiClient = $this->getServiceFromContainer(ApiClient::class);
        $applePayPaymentKeyId = null;
        $applePayPaymentCertificateId = null;

        // Upload Key
        if (is_null($errorMessage)) {
            try {
                $response = $apiClient->uploadApplePayPaymentKey($key);
                if ($response->getStatusCode() !== 201) {
                    $errorMessage = 'OSCUNZER_ERROR_TRANSMITTING_APPLEPAY_PAYMENT_SET_KEY';
                } else {
                    /** @var array{'id': string} $responseBody */
                    $responseBody = json_decode($response->getBody()->__toString());
                    $applePayPaymentKeyId = $responseBody['id'];
                }
            } catch (Throwable $loggerException) {
                $errorMessage = 'OSCUNZER_ERROR_TRANSMITTING_APPLEPAY_PAYMENT_SET_KEY';
            }
        }

        // Upload Certificate
        if ($applePayPaymentKeyId && is_null($errorMessage)) {
            try {
                $response = $apiClient->uploadApplePayPaymentCertificate($cert, $applePayPaymentKeyId);
                if ($response->getStatusCode() !== 201) {
                    $errorMessage = 'OSCUNZER_ERROR_TRANSMITTING_APPLEPAY_PAYMENT_SET_CERT';
                } else {
                    /** @var array{'id': string} $responseBody */
                    $responseBody = json_decode($response->getBody()->__toString());
                    $applePayPaymentCertificateId = $responseBody['id'];
                }
            } catch (Throwable $loggerException) {
                $errorMessage = 'OSCUNZER_ERROR_TRANSMITTING_APPLEPAY_PAYMENT_SET_CERT';
            }
        }

        // Activate Certificate
        if ($applePayPaymentKeyId && $applePayPaymentCertificateId && is_null($errorMessage)) {
            try {
                $response = $apiClient->activateApplePayPaymentCertificate($applePayPaymentCertificateId);
                if ($response->getStatusCode() !== 200) {
                    $errorMessage = 'OSCUNZER_ERROR_ACTIVATE_APPLEPAY_PAYMENT_CERT';
                } else {
                    $this->moduleSettings->saveApplePayPaymentKeyId($applePayPaymentKeyId);
                    $this->moduleSettings->saveApplePayPaymentCertificateId($applePayPaymentCertificateId);
                }
            } catch (Throwable $loggerException) {
                $errorMessage = 'OSCUNZER_ERROR_ACTIVATE_APPLEPAY_PAYMENT_CERT';
            }
        }

        if ($errorMessage) {
            Registry::getUtilsView()->addErrorToDisplay(
                oxNew(
                    UnzerException::class,
                    $this->translator->translate(
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
        $keyId = $this->moduleSettings->getApplePayPaymentKeyId();
        if ($this->moduleSettings->getApplePayMerchantCertKey() && $keyId) {
            try {
                $keyExists = $this->getServiceFromContainer(ApiClient::class)
                    ->requestApplePayPaymentKey($keyId)
                    ->getStatusCode()
                    === 200;
            } catch (GuzzleException $guzzleException) {
                Registry::getUtilsView()->addErrorToDisplay(
                    oxNew(
                        UnzerException::class,
                        $this->translator->translate(
                            'OSCUNZER_ERROR_TRANSMITTING_APPLEPAY_PAYMENT_GET_KEY'
                        )
                    )
                );
            }
        }
        return $keyExists;
    }

    /**
     * @throws GuzzleException
     */
    public function getApplePayPaymentProcessingCertExists(): bool
    {
        $certExists = false;
        $certId = $this->moduleSettings->getApplePayPaymentCertificateId();
        if ($this->moduleSettings->getApplePayMerchantCert() && $certId) {
            try {
                $certExists = $this->getServiceFromContainer(ApiClient::class)
                    ->requestApplePayPaymentCert($certId)
                    ->getStatusCode()
                    === 200;
            } catch (GuzzleException $guzzleException) {
                Registry::getUtilsView()->addErrorToDisplay(
                    oxNew(
                        UnzerException::class,
                        $this->translator->translate(
                            'OSCUNZER_ERROR_TRANSMITTING_APPLEPAY_PAYMENT_GET_CERT'
                        )
                    )
                );
            }
        }
        return $certExists;
    }

    /**
     * @return void
     * @throws FileException
     */
    public function saveConfVars()
    {
        $request = Registry::getRequest();
        if (
            $request->getRequestEscapedParameter('oxid') &&
            $request->getRequestEscapedParameter('oxid') === 'osc-unzer'
        ) {
            // the systemMode is very important, so we set it first ...
            /** @var array $confselects */
            $confselects = $request->getRequestEscapedParameter('confselects');
            /** @var string $systemMode */
            $systemMode = $confselects['UnzerSystemMode'];
            $this->moduleSettings->setSystemMode($systemMode);

            $this->resetContentCache();

            if (/** @var array $requestApplePayMC */
                $requestApplePayMC = $request->getRequestEscapedParameter('applePayMC')) {
                $this->moduleSettings->saveApplePayMerchantCapabilities($requestApplePayMC);
            }
            if (/** @var array $requestApplePayNetworks */
                $requestApplePayNetworks = $request->getRequestEscapedParameter('applePayNetworks')) {
                $this->moduleSettings->saveApplePayNetworks($requestApplePayNetworks);
            }
            if ($requestApplePayMerchantCert = $request->getRequestEscapedParameter('applePayMerchantCert')) {
                file_put_contents(
                    $this->moduleSettings->getApplePayMerchantCertFilePath(),
                    $requestApplePayMerchantCert
                );
            }
            if ($requestApplePayMerchantCertKey = $request->getRequestEscapedParameter('applePayMerchantCertKey')) {
                file_put_contents(
                    $this->moduleSettings->getApplePayMerchantCertKeyFilePath(),
                    $requestApplePayMerchantCertKey
                );
            }
        }

        parent::saveConfVars();
    }
}
