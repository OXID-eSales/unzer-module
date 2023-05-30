<?php

namespace OxidSolutionCatalysts\Unzer\Controller\Admin;

use GuzzleHttp\Exception\GuzzleException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Core\Exception\FileException;
use OxidSolutionCatalysts\Unzer\Exception\UnzerException;
use OxidSolutionCatalysts\Unzer\Module;
use OxidSolutionCatalysts\Unzer\Service\ApiClient;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Service\UnzerWebhooks;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use Throwable;

/**
 * Order class wrapper for Unzer module
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ModuleConfiguration extends ModuleConfiguration_parent
{
    use ServiceContainer;

    /** @var Translator $translator */
    protected $translator = null;
    /** @var ModuleSettings $moduleSettings */
    protected $moduleSettings = null;
    /** @var UnzerWebhooks $unzerWebhooks */
    protected $unzerWebhooks = null;
    protected string $_sModuleId; // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->translator = $this->getServiceFromContainer(Translator::class);
        $this->moduleSettings = $this->getServiceFromContainer(ModuleSettings::class);
        $this->unzerWebhooks = $this->getServiceFromContainer(UnzerWebhooks::class);
    }

    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function render(): string
    {
        parent::render();

        if ($this->_sModuleId == Module::MODULE_ID) {
            try {
                $this->_aViewData["webhookConfiguration"] = $this->moduleSettings->getWebhookConfiguration();
                $this->_aViewData['applePayMC'] = $this->moduleSettings->getApplePayMerchantCapabilities();
                $this->_aViewData['applePayNetworks'] = $this->moduleSettings->getApplePayNetworks();
                $this->_aViewData['applePayMerchantCert'] = $this->moduleSettings->getApplePayMerchantCert();
                $this->_aViewData['applePayMerchantCertKey'] = $this->moduleSettings->getApplePayMerchantCertKey();
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

    public function registerWebhooks(): void
    {
        try {
            $this->unzerWebhooks->setPrivateKeys(
                $this->moduleSettings->getPrivateKeysWithContext()
            );
            $this->unzerWebhooks->registerWebhookConfiguration();
        } catch (Throwable $loggerException) {
            Registry::getUtilsView()->addErrorToDisplay(
                $loggerException->getMessage()
            );
        }
    }

    public function unregisterWebhooks(): void
    {
        try {
            $this->unzerWebhooks->setPrivateKeys(
                $this->moduleSettings->getPrivateKeysWithContext()
            );
            $this->unzerWebhooks->unregisterWebhookConfiguration();
        } catch (Throwable $loggerException) {
            Registry::getUtilsView()->addErrorToDisplay(
                $loggerException->getMessage()
            );
        }
    }

    /**
     * @throws GuzzleException
     * @throws \JsonException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function transferApplePayPaymentProcessingData(): void
    {
        /** @var string $key */
        $key = Registry::getRequest()->getRequestEscapedParameter('applePayPaymentProcessingCertKey');
        /** @var string $cert */
        $cert = Registry::getRequest()->getRequestEscapedParameter('applePayPaymentProcessingCert');
        $errorMessage = !$key || !$cert ? 'OSCUNZER_ERROR_TRANSMITTING_APPLEPAY_PAYMENT_SET_CERT' : null;

        $apiClient = $this->getServiceFromContainer(ApiClient::class);
        $applePayKeyId = null;
        $applePayCertId = null;

        // Upload Key
        if (is_null($errorMessage)) {
            try {
                $response = $apiClient->uploadApplePayPaymentKey($key);
                if ($response->getStatusCode() !== 201) {
                    $errorMessage = 'OSCUNZER_ERROR_TRANSMITTING_APPLEPAY_PAYMENT_SET_KEY';
                } else {
                    /** @var array{'id': string} $responseBody */
                    $responseBody = json_decode($response->getBody()->__toString(), true);
                    $applePayKeyId = $responseBody['id'];
                }
            } catch (Throwable $loggerException) {
                $errorMessage = 'OSCUNZER_ERROR_TRANSMITTING_APPLEPAY_PAYMENT_SET_KEY';
            }
        }

        // Upload Certificate
        if ($applePayKeyId && is_null($errorMessage)) {
            try {
                $response = $apiClient->uploadApplePayPaymentCertificate($cert, $applePayKeyId);
                if ($response->getStatusCode() !== 201) {
                    $errorMessage = 'OSCUNZER_ERROR_TRANSMITTING_APPLEPAY_PAYMENT_SET_CERT';
                } else {
                    /** @var array{'id': string} $responseBody */
                    $responseBody = json_decode($response->getBody()->__toString(), true);
                    $applePayCertId = $responseBody['id'];
                }
            } catch (Throwable $loggerException) {
                $errorMessage = 'OSCUNZER_ERROR_TRANSMITTING_APPLEPAY_PAYMENT_SET_CERT';
            }
        }

        // Activate Certificate
        if ($applePayKeyId && $applePayCertId && is_null($errorMessage)) {
            try {
                $response = $apiClient->activateApplePayPaymentCertificate($applePayCertId);
                if ($response->getStatusCode() !== 200) {
                    $errorMessage = 'OSCUNZER_ERROR_ACTIVATE_APPLEPAY_PAYMENT_CERT';
                } else {
                    $this->moduleSettings->saveApplePayPaymentKeyId($applePayKeyId);
                    $this->moduleSettings->saveApplePayPaymentCertificateId($applePayCertId);
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
     * @SuppressWarnings(PHPMD.StaticAccess)
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

            $applePayMC = $request->getRequestEscapedParameter('applePayMC');
            if (is_array($applePayMC)) {
                $this->moduleSettings->saveApplePayMerchantCapabilities($applePayMC);
            }
            $applePayNetworks = $request->getRequestEscapedParameter('applePayNetworks');
            if (is_array($applePayNetworks)) {
                $this->moduleSettings->saveApplePayNetworks($applePayNetworks);
            }
            $certConfigKey = $this->moduleSettings->getSystemMode() . '-' . 'applePayMerchantCert';
            $applePayMerchantCert = $request->getRequestEscapedParameter($certConfigKey);
            file_put_contents(
                $this->moduleSettings->getApplePayMerchantCertFilePath(),
                $applePayMerchantCert
            );
            $keyConfigKey = $this->moduleSettings->getSystemMode() . '-' . 'applePayMerchantCertKey';
            $applePayMerchCertKey = $request->getRequestEscapedParameter($keyConfigKey);
            file_put_contents(
                $this->moduleSettings->getApplePayMerchantCertKeyFilePath(),
                $applePayMerchCertKey
            );
        }

        parent::saveConfVars();
    }
}
