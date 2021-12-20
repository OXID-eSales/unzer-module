<?php

namespace OxidSolutionCatalysts\Unzer\Exception;

use Exception;

class Redirect extends Exception
{
    /** @var string */
    private $destination;

    public function __construct(string $destination)
    {
        $this->destination = $destination;

        parent::__construct();
    }

    public function getDestination(): string
    {
        return $this->destination;
    }
}
