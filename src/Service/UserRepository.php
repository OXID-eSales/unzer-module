<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\Eshop\Application\Model\Country;

class UserRepository {
    /** @var EshopRegistry */
    private $registry;

    public function __construct(
        EshopRegistry $registry
    )
    {
        $this->registry = $registry;
    }

    public function getUserCountryIso(): string
    {
        $result = '';
        if ($user = $this->registry->getSession()->getUser()) {
            $country = oxNew(Country::class);
            $country->load($user->getFieldData('oxcountryid'));
            $result = (string) $country->getFieldData('oxisoalpha2');
        }
        return $result;
    }
}