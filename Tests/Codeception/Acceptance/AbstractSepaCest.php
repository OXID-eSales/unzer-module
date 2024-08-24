<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

abstract class AbstractSepaCest extends BaseCest
{
    protected string $sepaPaymentLabel = "//label[@for='payment_oscunzer_sepa']";
    protected string $IBANInput = "//input[contains(@id, 'unzer-iban-input')]";

    protected function getOXID(): array
    {
        return ['oscunzer_sepa'];
    }
}
