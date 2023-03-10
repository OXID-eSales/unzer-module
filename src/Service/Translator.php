<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Core\Language;

class Translator
{
    /** @var Language */
    private $language;

    /**
     * @param Language $language
     */
    public function __construct(Language $language)
    {
        $this->language = $language;
    }

    /**
     * @param string $languageCode
     * @param string $defaultMessage
     * @return string
     */
    public function translateCode(string $languageCode, string $defaultMessage): string
    {
        if (
            substr_compare($languageCode, "API.", 0, 4) == 0
            || substr_compare($languageCode, "COR.", 0, 4) == 0
            || substr_compare($languageCode, "SDM.", 0, 4) == 0
        ) {
            $languageCode = substr($languageCode, 4);
        }

        $languageCode = 'oscunzer_' . $languageCode;
        $translation = $this->translate($languageCode);
        if (!$this->language->isTranslated()) {
            $translation = $defaultMessage;
        }

        return $translation;
    }

    /**
     * @param string $message
     * @return string
     */
    public function translate(string $message)
    {
        /** @var string $translate */
        $translate = $this->language->translateString($message, null, false);
        return $translate;
    }

    /**
     * @param float $amount
     * @return string
     */
    public function formatCurrency(float $amount): string
    {
        return $this->language->formatCurrency($amount);
    }
}
