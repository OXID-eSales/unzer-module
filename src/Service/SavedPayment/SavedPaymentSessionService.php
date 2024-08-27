<?php

namespace OxidSolutionCatalysts\Unzer\Service\SavedPayment;

use OxidEsales\Eshop\Core\Session;
use OxidSolutionCatalysts\Unzer\Service\RequestService;

class SavedPaymentSessionService
{
    private Session $session;
    private RequestService $requestService;

    public function __construct(Session $session, RequestService $requestService)
    {
        $this->session = $session;
        $this->requestService = $requestService;
    }

    public function isSavedPayment(): bool
    {
        return (bool) $this->session->getVariable($this->getSessionVariableName());
    }

    public function setSavedPayment(): void
    {
        $this->session->setVariable(
            $this->getSessionVariableName(),
            $this->requestService->isSavePaymentSelectedByUserInRequest()
        );
    }

    public function unsetSavedPayment(): void
    {
        $this->session->deleteVariable(
            $this->getSessionVariableName()
        );
    }

    private function getSessionVariableName(): string
    {
        return self::class . '_userClickedSavePaymentCheckbox';
    }
}
