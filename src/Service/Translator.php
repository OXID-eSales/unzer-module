<?php

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

    public function translate(string $languageCode, string $defaultMessage): string
    {
        if (
            substr_compare($languageCode, "API.", 0, 4) == 0
            || substr_compare($languageCode, "COR.", 0, 4) == 0
            || substr_compare($languageCode, "SDM.", 0, 4) == 0
        ) {
            $languageCode = substr($languageCode, 4);
        }
        $languageCode = 'oscunzer_' . $languageCode;
        $translation = $this->language->translateString($languageCode);
        if (!$this->language->isTranslated()) {
            $translation = $defaultMessage;
        }

        return $translation;
    }
}
