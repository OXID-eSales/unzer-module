<?php

namespace OxidSolutionCatalysts\Unzer\Controller\Admin;

use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Core\Exception\FileException;
use OxidSolutionCatalysts\Unzer\Traits\Request;
use OxidSolutionCatalysts\Unzer\Service\ModuleConfiguration\ApplePaymentProcessingCertificate;
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
 * TODO: Fix all the suppressed warnings
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class ModuleConfiguration extends ModuleConfiguration_parent
{
    use ServiceContainer;
    use Request;

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
    public function render()
    {
        $result = parent::render();

        if ($this->_sModuleId === Module::MODULE_ID) {
            try {
                $this->_aViewData["webhookConfiguration"] =
                    $this->moduleSettings->getWebhookConfiguration();
                $this->_aViewData['applePayMC'] =
                    $this->moduleSettings->getApplePayMerchantCapabilities();
                $this->_aViewData['applePayNetworks'] =
                    $this->moduleSettings->getApplePayNetworks();
                $this->_aViewData['applePayMerchantCert'] =
                    $this->moduleSettings->getApplePayMerchantCert();
                $this->_aViewData['applePayMerchantCertKey'] =
                    $this->moduleSettings->getApplePayMerchantCertKey();
                $this->_aViewData['applePayPaymentProcessingCert'] =
                    $this->moduleSettings->getApplePayPaymentCert();
                $this->_aViewData['applePayPaymentProcessingCertKey'] =
                    $this->moduleSettings->getApplePayPaymentPrivateKey();
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
        return $result;
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function transferApplePayPaymentProcessingData(): void
    {
        $systemMode = $this->moduleSettings->getSystemMode();
        $keyReqName = $systemMode . '-' . 'applePayPaymentProcessingCertKey';
        $key = $this->getUnzerStringRequestParameter($keyReqName);
        $certReqName = $systemMode . '-' . 'applePayPaymentProcessingCert';
        $cert = $this->getUnzerStringRequestParameter($certReqName);
        $errorMessage = !$key || !$cert ? 'OSCUNZER_ERROR_TRANSMITTING_APPLEPAY_PAYMENT_SET_CERT' : null;

        $apiClient = $this->getServiceFromContainer(ApiClient::class);
        $applePayKeyId = null;
        $applePayCertId = null;

        // save Apple Pay processing cert and key
        if (is_null($errorMessage)) {
            $applePaymentProcessingCertificateService = $this->getServiceFromContainer(
                ApplePaymentProcessingCertificate::class
            );
            $applePaymentProcessingCertificateService->saveCertificate($cert);
            $applePaymentProcessingCertificateService->saveCertificateKey($key);
        }

        // Upload Key
        if (is_null($errorMessage)) {
            try {
                $response = $apiClient->uploadApplePayPaymentKey($key);
                if (!$response || $response->getStatusCode() !== 201) {
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
                if (!$response || $response->getStatusCode() !== 201) {
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
                if (!$response || $response->getStatusCode() !== 200) {
                    $errorMessage = 'OSCUNZER_ERROR_ACTIVATE_APPLEPAY_PAYMENT_CERT';
                } else {
                    $this->moduleSettings->saveApplePayPaymentCertificateId($applePayCertId);
                    $this->moduleSettings->saveApplePayPaymentKeyId($applePayKeyId);
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OxidEsales\EshopCommunity\Core\Exception\FileException
     */
    public function getApplePayPaymentProcessingKeyExists(): bool
    {
        $keyId = $this->moduleSettings->getApplePayPaymentKeyId();
        if ($this->moduleSettings->getApplePayMerchantCertKey() && $keyId) {
            try {
                $response = $this->getServiceFromContainer(ApiClient::class)
                    ->requestApplePayPaymentKey($keyId);
                if (!$response) {
                    return false;
                }
                return $response->getStatusCode() === 200;
            } catch (GuzzleException | JsonException $guzzleException) {
                $this->addErrorTransmittingKey();
            }
        }
        return false;
    }

    /**
     * @throws GuzzleException
     * @throws FileException
     */
    public function getApplePayPaymentProcessingCertExists(): bool
    {
        $certId = $this->moduleSettings->getApplePayPaymentCertificateId();
        if ($this->moduleSettings->getApplePayMerchantCert() && $certId) {
            try {
                $response = $this->getServiceFromContainer(ApiClient::class)
                    ->requestApplePayPaymentCert($certId);
                if (!$response) {
                    $this->addErrorTransmittingCertificate();
                    return false;
                }

                return $response->getStatusCode() === 200;
            } catch (GuzzleException | JsonException $guzzleException) {
                $this->addErrorTransmittingCertificate();
            }
        }
        return false;
    }

    /**
     * @return void
     * @throws FileException
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function saveConfVars()
    {
        $moduleId = $this->getUnzerStringRequestEscapedParameter('oxid');
        if ($moduleId === Module::MODULE_ID) {
            // get translated systemmode
            $systemMode = $this->moduleSettings->getSystemMode();

            $applePayMC = $this->getUnzerArrayRequestParameter('applePayMC');
            $this->moduleSettings->saveApplePayMerchantCapabilities($applePayMC);
            $applePayNetworks = $this->getUnzerArrayRequestParameter('applePayNetworks');
            $this->moduleSettings->saveApplePayNetworks($applePayNetworks);
            $certConfigKey = $systemMode . '-' . 'applePayMerchantCert';
            $applePayMerchantCert = $this->getUnzerStringRequestEscapedParameter($certConfigKey);
            file_put_contents(
                $this->moduleSettings->getApplePayMerchantCertFilePath(),
                $applePayMerchantCert
            );

            $keyConfigKey = $systemMode . '-' . 'applePayMerchantCertKey';
            $applePayMerchCertKey = $this->getUnzerStringRequestEscapedParameter($keyConfigKey);
            file_put_contents(
                $this->moduleSettings->getApplePayMerchantCertKeyFilePath(),
                $applePayMerchCertKey
            );

            $applePaymentProcessingCertificateService = $this->getServiceFromContainer(
                ApplePaymentProcessingCertificate::class
            );
            $applePaymentProcessingCertificateService->saveCertificate(
                $this->getUnzerStringRequestEscapedParameter($systemMode . '-' . 'applePayPaymentProcessingCert')
            );

            $applePaymentProcessingCertificateService->saveCertificateKey(
                $this->getUnzerStringRequestEscapedParameter($systemMode . '-' . 'applePayPaymentProcessingCertKey')
            );

            $this->moduleSettings->saveWebhookConfiguration([]);
            $this->registerWebhooks();
        }
        parent::saveConfVars();
    }

    private function addErrorTransmittingCertificate(): void
    {
        Registry::getUtilsView()->addErrorToDisplay(
            oxNew(
                UnzerException::class,
                $this->translator->translate(
                    'OSCUNZER_ERROR_TRANSMITTING_APPLEPAY_PAYMENT_GET_CERT'
                )
            )
        );
    }

    private function addErrorTransmittingKey(): void
    {
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
