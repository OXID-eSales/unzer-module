<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Core\Language;

class Translator
{
    /** @var Language */
    private $language;

    public function __construct(Language $language)
    {
        $this->language = $language;
    }

    public function translate(string $languageCode, string $defaultMessage)
    {
        $string = 'oscunzer_' . substr($languageCode, 4);
        $translation = $this->language->translateString($string);
        if (!$this->language->isTranslated()) {
            $translation = $defaultMessage;
        }

        return $translation;
    }
}
