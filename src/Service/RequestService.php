<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Core\Request;

class RequestService
{
    /** @var Request $oxidRequest */
    private $oxidRequest;

    public function __construct(Request $oxidRequest)
    {
        $this->oxidRequest = $oxidRequest;
    }
    public function isSavePaymentSelectedByUserInRequest(): bool
    {
        return $this->getUnzerBoolRequestParameter('oscunzersavepayment');
    }

    private function getUnzerBoolRequestParameter(string $varName): bool
    {
        return (bool) $this->oxidRequest->getRequestParameter($varName);
    }
}
