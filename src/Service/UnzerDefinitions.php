<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidSolutionCatalysts\Unzer\Core\UnzerDefinitions as CoreUnzerDefinitions;

class UnzerDefinitions
{
    protected CoreUnzerDefinitions $unzerDefinitions;

    public function __construct()
    {
        $this->unzerDefinitions = new CoreUnzerDefinitions();
    }

    public function getDefinitionsArray(): array
    {
        return $this->unzerDefinitions->getUnzerDefinitions();
    }

    public function getStaticContents(): array
    {
        return $this->unzerDefinitions->getUnzerStaticContents();
    }

    public function getRdfaDefinitions(): array
    {
        return $this->unzerDefinitions->getUnzerRdfaDefinitions();
    }

    public function getCompanyTypes(): array
    {
        return $this->unzerDefinitions->getUnzerCompanyTypes();
    }

    public function getPaymentAbilities(): array
    {
        return CoreUnzerDefinitions::PAYMENT_ABILITIES;
    }

    public function isUnzerType(string $sTypeId): bool
    {
        $unzerDefinitions = $this->getDefinitionsArray();
        return isset($unzerDefinitions[ $sTypeId ]);
    }

    public function unzerTypeHasAbility(string $sTypeId, string $sAbility): bool
    {
        return $this->checkAbility($sTypeId, $sAbility);
    }

    private function checkAbility(string $sTypeId, string $sAbility): bool
    {
        if ($this->isUnzerType($sTypeId)) {
            $unzerDefinitions = $this->getDefinitionsArray();
            return in_array($sAbility, $unzerDefinitions[$sTypeId]['abilities']);
        }
        return false;
    }
}
