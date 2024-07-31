<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use UnzerSDK\Resources\TransactionTypes\Charge;
use OxidEsales\Eshop\Core\Session;

class PrePaymentBankAccountService
{
    private Session $session;

    private const SESSION_VARIABLE_PREFIX = 'PrePaymentBankAccountService';

    public function __construct(Session $session)
    {
        $this->session = $session;
    }
    public function persistBankAccountInfo(Charge $charge)
    {
        // in unzer sdk it's called orderId in oxid: oxunzerordernr
        if ($charge->getIban()) {
            $this->session->setVariable(
                $this->getSessionVariableName($charge->getOrderId(), 'iban'),
                $charge->getIban()
            );
        }

        if ($charge->getBic()) {
            $this->session->setVariable(
                $this->getSessionVariableName($charge->getOrderId(), 'bic'),
                $charge->getBic()
            );
        }

        if ($charge->getHolder()) {
            $this->session->setVariable(
                $this->getSessionVariableName($charge->getOrderId(), 'holder'),
                $charge->getHolder()
            );
        }
    }

    public function getIban(string $unzerOrderNumber): ?string
    {
        return $this->session->getVariable(
            $this->getSessionVariableName($unzerOrderNumber, 'iban')
        );
    }

    public function getBic(string $unzerOrderNumber): ?string
    {
        return $this->session->getVariable(
            $this->getSessionVariableName($unzerOrderNumber, 'bic')
        );
    }

    public function getHolder(string $unzerOrderNumber): ?string
    {
        return $this->session->getVariable(
            $this->getSessionVariableName($unzerOrderNumber, 'holder')
        );
    }

    private function getSessionVariableName(string $unzerOrderNumber, string $variableName): string
    {
        return self::SESSION_VARIABLE_PREFIX . '_' . $unzerOrderNumber . '_' . $variableName;
    }
}
