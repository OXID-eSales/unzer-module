<?php

namespace OxidSolutionCatalysts\Unzer\Exception;

class RedirectWithMessage extends Redirect
{
    /** @var string */
    private $messageKey;

    /** @var array */
    private $messageParams;

    public function __construct(string $destination, string $messageKey, array $messageParams = [])
    {
        parent::__construct($destination);

        $this->messageKey = $messageKey;
        $this->messageParams = $messageParams;
    }

    public function getMessageKey(): string
    {
        return $this->messageKey;
    }

    public function getMessageParams(): array
    {
        return $this->messageParams;
    }
}
