<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Exception;

class Redirect extends UnzerException
{
    /** @var string */
    private $destination;

    /**
     * @param string $destination
     */
    public function __construct(string $destination)
    {
        $this->destination = $destination;

        parent::__construct();
    }

    /**
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }
}
