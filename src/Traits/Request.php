<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\modules\osc\unzer\src\Traits;

use OxidEsales\EshopCommunity\Core\Registry;

trait Request
{
    protected function getUnzerStringRequestEscapedParameter(string $varName, string $defaultValue = ''): string
    {
        $value = Registry::getRequest()->getRequestEscapedParameter($varName, $defaultValue);
        return is_string($value) ? $value : '';
    }

    protected function getUnzerStringRequestParameter(string $varName, string $defaultValue = ''): string
    {
        $value = Registry::getRequest()->getRequestParameter($varName, $defaultValue);
        return is_string($value) ? $value : '';
    }

    protected function getUnzerArrayRequestParameter(string $varName, array $defaultValue = []): array
    {
        $value = Registry::getRequest()->getRequestParameter($varName, $defaultValue);
        return is_array($value) ? $value : [];
    }

    protected function getUnzerFloatRequestParameter(string $varName, float $defaultValue = 0.0): float
    {
        $value = Registry::getRequest()->getRequestParameter($varName, $defaultValue);
        $value = is_string($value) ? $this->normalizeNumber($value) : $value;
        return is_numeric($value) ? (float) $value : 0.0;
    }

    protected function getUnzerBoolRequestParameter(string $varName, bool $defaultValue = false): bool
    {
        $value = Registry::getRequest()->getRequestParameter($varName, $defaultValue);
        return $value ?: false;
    }

    private function normalizeNumber(string $input): float
    {
        $input = str_replace(' ', '', $input);
        $lastCommaPos = strrpos($input, ',');

        if ($lastCommaPos !== false) {
            if (strpos($input, '.', $lastCommaPos) !== false) {
                $number = str_replace(',', '', $input);
            } else {
                $number = substr_replace($input, '.', $lastCommaPos, 1);
                $number = str_replace(',', '', $number);
            }
        } else {
            $number = $input;
        }

        return (float)$number;
    }
}
