<?php

namespace OxidSolutionCatalysts\Unzer\Exception;

class RedirectWithUnzerCodeMessage extends Redirect
{
    /** @var string */
    private $unzerErrorCode;

    /** @var string */
    private $defaultMessage;

    public function __construct(string $destination, string $unzerCode, string $defaultMessage = '')
    {
        parent::__construct($destination);

        $this->unzerErrorCode = $unzerCode;
        $this->defaultMessage = $defaultMessage;
    }

    public function getUnzerErrorCode(): string
    {
        return $this->unzerErrorCode;
    }

    public function getDefaultMessage(): string
    {
        return $this->defaultMessage;
    }
}
