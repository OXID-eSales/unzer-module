<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\Eshop\Application\Model\Country;

class UserRepository
{
    /** @var EshopRegistry */
    private $registry;

    public function __construct(
        EshopRegistry $registry
    ) {
        $this->registry = $registry;
    }

    public function getUserCountryIso(): string
    {
        $result = '';
        $user = $this->registry->getSession()->getUser();
        if (null != $user) {
            $country = oxNew(Country::class);
            /** @var string $countryId */
            $countryId = $user->getFieldData('oxcountryid');
            $country->load($countryId);
            /** @var string $result */
            $result = $country->getFieldData('oxisoalpha2');
        }
        return $result;
    }
}
