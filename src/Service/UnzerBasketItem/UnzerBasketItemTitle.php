<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Service\UnzerBasketItem;

use OxidSolutionCatalysts\Unzer\Service\Translator;

class UnzerBasketItemTitle
{
    private Translator $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function getTitle(float $amount): string
    {
        return $this->translator->translate($amount < 0. ? 'SURCHARGE' : 'DISCOUNT');
    }
}
