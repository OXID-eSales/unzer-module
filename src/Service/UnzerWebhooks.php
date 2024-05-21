<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Core\Registry;
use UnzerSDK\Constants\WebhookEvents;
use UnzerSDK\Resources\Webhook;
use UnzerSDK\Unzer as UnzerSDK;
use Throwable;

class UnzerWebhooks
{
    protected UnzerSDKLoader $unzerSDKLoader;
    protected ModuleSettings $moduleSettings;

    protected array $privateKeys = [];
    protected string $event = WebhookEvents::PAYMENT;

    public function __construct(
        UnzerSDKLoader $unzerSDKLoader,
        ModuleSettings $moduleSettings
    ) {
        $this->unzerSDKLoader = $unzerSDKLoader;
        $this->moduleSettings = $moduleSettings;
    }

    /**
     * Each private key must have a unique index, used as "context" for the webhook url.
     * @param array $privateKeys
     * @return $this
     */
    public function setPrivateKeys(array $privateKeys): self
    {
        $this->privateKeys = $privateKeys;
        return $this;
    }

    /**
     * Default event is "WebhookEvents::PAYMENT"
     * @param string $event
     * @return $this
     */
    public function setEvent(string $event): self
    {
        $this->event = $event;
        return $this;
    }

    /**
     * Register webhooks, based on a private key array.
     * @return void
     * @throws Throwable
     */
    public function registerWebhookConfiguration(): void
    {
        $this->cleanOldWebhook();
        $webhookConfiguration = $this->moduleSettings->getWebhookConfiguration();
        foreach ($this->privateKeys as $context => $privKey) {
            $url = $this->getWebhookURL(['context' => $context]);
            $unzerWebhooks = $this->getUnzerWebhooksByKey($privKey);
            $registeredWebhook = $this->findWebhookByUrlAndEvent($url, $this->event, $unzerWebhooks);
            $storedWebhook = $this->findWebhookByUrlAndEventAndKey($url, $this->event, $privKey, $webhookConfiguration);

            if (empty($storedWebhook)) {
                if (empty($registeredWebhook)) {
                    $registeredWebhook = $this->createWebhookForKey($privKey, $url, $this->event);
                }
            }
            if (!empty($storedWebhook) && $storedWebhook['id'] != $registeredWebhook['id']) {
                $this->deleteWebhookForKey($privKey, $storedWebhook['id']);
            }
            $registeredWebhook['key'] = $privKey;
            $registeredWebhook['context'] = $context;
            $this->addWebhookConfiguration($privKey, $registeredWebhook);
        }
    }

    /**
     * Unregister webhooks, based on the current configuration.
     * @return void
     * @throws Throwable
     */
    public function unregisterWebhookConfiguration(): void
    {
        $webhookConfiguration = $this->moduleSettings->getWebhookConfiguration();
        foreach ($webhookConfiguration as $webhookConfig) {
            $this->deleteWebhookForKey($webhookConfig['key'], $webhookConfig['id']);
        }
        $this->moduleSettings->saveWebhookConfiguration([]);
    }

    /**
     * @param string $context
     * @return string
     */
    public function getUnzerKeyFromWebhookContext(string $context): string
    {
        $foundKey = '';
        $mappedConfiguration = $this->getMappedWebhookConfiguration();
        foreach ($mappedConfiguration as $config) {
            if ($config['context'] === $context) {
                $foundKey = $config['key'];
            }
        }
        return $foundKey;
    }

    protected function getUnzerSDKbyKey(string $unzerKey): UnzerSDK
    {
        return $this->unzerSDKLoader->getUnzerSDKbyKey($unzerKey);
    }

    public function getMappedWebhookConfiguration(): array
    {
        $resultConfig = [];
        $webhookConfiguration = $this->moduleSettings->getWebhookConfiguration();
        foreach ($webhookConfiguration as $webhookConfig) {
            $key = $webhookConfig['key'];
            $resultConfig[$key] = $webhookConfig;
        }
        return $resultConfig;
    }

    protected function saveMappedWebhookConfiguration(array $mappedConfig): void
    {
        $webhookConfig = array_values($mappedConfig);
        $this->moduleSettings->saveWebhookConfiguration($webhookConfig);
    }

    public function addWebhookConfiguration(string $key, array $config): void
    {
        $mappedConfig = $this->getMappedWebhookConfiguration();
        $mappedConfig[$key] = $config;
        $this->saveMappedWebhookConfiguration($mappedConfig);
    }

    public function removeWebhookConfiguration(string $key): void
    {
        $mappedConfig = $this->getMappedWebhookConfiguration();
        if (isset($mappedConfig[$key])) {
            unset($mappedConfig[$key]);
            $this->saveMappedWebhookConfiguration($mappedConfig);
        }
    }

    public function getWebhookURL(array $extraParams): string
    {
        $withXDebug = ($this->moduleSettings->isSandboxMode() && $this->moduleSettings->isDebugMode());
        $extra = '';
        if (!empty($extraParams)) {
            $extra = '&' . http_build_query($extraParams);
        }
        return Registry::getConfig()->getSslShopUrl()
            . 'index.php?cl=unzer_dispatcher&fnc=updatePaymentTransStatus'
            . $extra
            . ($withXDebug ? '&XDEBUG_SESSION_START' : '');
    }

    protected function cleanOldWebhook(): void
    {
        // v1.0.0 - the original URL and payment method
        $shopKey = $this->moduleSettings->getStandardPrivateKey();
        $orgEvent = WebhookEvents::PAYMENT;
        $orgUrl = $this->getWebhookURL([]);
        $unzerWebhooks = $this->getUnzerWebhooksByKey($shopKey);
        $foundWebhook = $this->findWebhookByUrlAndEvent($orgUrl, $orgEvent, $unzerWebhooks);
        if ($foundWebhook && !empty($foundWebhook['id'])) {
            $unzer = $this->getUnzerSDKbyKey($shopKey);
            $unzer->deleteWebhook($foundWebhook['id']);
        }
    }

    public function createWebhookForKey(string $unzerKey, string $url, string $event): array
    {
        try {
            /** @var UnzerSDK $unzer */
            $unzer = $this->getUnzerSDKbyKey($unzerKey);
            $result = $unzer->createWebhook($url, $event);
            /** @var string $resultId */
            $resultId = $result->getId();

            return [
                'id' => $resultId,
                'url' => $url,
                'event' => $event,
                'key' => $unzerKey,
            ];
        } catch (Throwable $loggerException) {
            throw $loggerException;
        }
    }

    public function deleteWebhookForKey(string $unzerKey, string $webhookId): void
    {
        try {
            /** @var UnzerSDK $unzer */
            $unzer = $this->getUnzerSDKbyKey($unzerKey);
            $unzer->deleteWebhook($webhookId);
        } catch (Throwable $loggerException) {
            throw $loggerException;
        }
    }

    public function getUnzerWebhooksByKey(string $unzerKey): array
    {
        $allHooks = [];
        try {
            /** @var UnzerSDK $unzer */
            $unzer = $this->getUnzerSDKbyKey($unzerKey);
            $webhooks = $unzer->fetchAllWebhooks();
            /** @var Webhook $webhook */
            foreach ($webhooks as $webhook) {
                $webhookId = $webhook->getId();
                $allHooks[ $webhookId ] = [
                    'id' => $webhookId,
                    'url' => $webhook->getUrl(),
                    'event' => $webhook->getEvent(),
                ];
            }
        } catch (Throwable $loggerException) {
            throw $loggerException;
        }
        return $allHooks;
    }

    public function findWebhookByUrlAndEvent(string $url, string $event, array $webhooks): array
    {
        foreach ($webhooks as $webhook) {
            if ($url === $webhook['url'] && $event === $webhook['event']) {
                return $webhook;
            }
        }
        return [];
    }

    public function findWebhookByUrlAndEventAndKey(string $url, string $event, string $key, array $webhooks): array
    {
        foreach ($webhooks as $webhook) {
            if ($url === $webhook['url'] && $event === $webhook['event'] && $key === $webhook['key']) {
                return $webhook;
            }
        }
        return [];
    }
}
