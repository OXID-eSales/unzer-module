<?php

namespace OxidSolutionCatalysts\Unzer\Exception;

class MessageRedirect extends Redirect
{
    /** @var string */
    private $messageKey;

    /** @var string */
    private $defaultMessage;

    public function __construct(string $destination, string $messageKey, string $defaultMessage = '')
    {
        parent::__construct($destination);

        $this->messageKey = $messageKey;
        $this->defaultMessage = $defaultMessage;
    }

    public function getMessageKey(): string
    {
        return $this->messageKey;
    }

    public function getDefaultMessage(): string
    {
        return $this->defaultMessage;
    }
}
