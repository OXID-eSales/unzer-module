<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Core;

use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use phpDocumentor\Reflection\Types\True_;

class ViewConfig extends ViewConfig_parent
{
    use ServiceContainer;

    /**
     * Returns System Mode live|sandbox.
     *
     * @return string
     */
    public function getUnzerSystemMode(): string
    {
        return $this->getServiceFromContainer(ModuleSettings::class)->getSystemMode();
    }

    /**
     * @return bool
     */
    public function isUnzerDebugMode(): bool
    {
        return $this->getServiceFromContainer(ModuleSettings::class)->isDebugMode();
    }

    /**
     * Returns unzer public key.
     *
     * @return string
     */
    public function getUnzerPubKey(): string
    {
        return $this->getServiceFromContainer(ModuleSettings::class)->getShopPublicKey();
    }

    /**
     * Returns unzer private key.
     *
     * @return string
     */
    public function getUnzerPrivKey(): string
    {
        return $this->getServiceFromContainer(ModuleSettings::class)->getShopPrivateKey();
    }

    /**
     * retrieve additional payment information from session
     *
     * @return string
     */
    public function getSessionPaymentInfo(): string
    {
        return Registry::getSession()->getVariable('additionalPaymentInformation');
    }

    /**
     * Returns unzer Installment Rate.
     *
     * @return float
     */
    public function getUnzerInstallmentRate(): float
    {
        return $this->getServiceFromContainer(ModuleSettings::class)->getInstallmentRate();
    }

    /**
     * checks if jQuery should be imported
     *
     * @return bool
     */
    public function useModuleJQueryInFrontend(): bool
    {
        return $this->getServiceFromContainer(ModuleSettings::class)->useModuleJQueryInFrontend();
    }
}
