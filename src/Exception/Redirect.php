<?php

namespace OxidSolutionCatalysts\Unzer\Exception;

class Redirect extends UnzerException
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
