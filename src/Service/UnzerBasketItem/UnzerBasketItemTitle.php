<?php

namespace OxidSolutionCatalysts\Unzer\Service\UnzerBasketItem;

use OxidSolutionCatalysts\Unzer\Service\Translator;

class UnzerBasketItemTitle
{
    /** @var Translator $translator */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function getTitle(float $amount): string
    {
        return $this->translator->translate($amount < 0. ? 'SURCHARGE' : 'DISCOUNT');
    }
}
