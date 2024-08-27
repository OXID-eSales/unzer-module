<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance\Traits;

use OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance\Helper\SeleniumBasketModalHelper;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

trait BasketModalSettingTrait
{
    /** @var SeleniumBasketModalHelper $basketModelHelper */
    private $basketModelHelper;

    public function _before(AcceptanceTester $I): void
    {
        $this->basketModelHelper = new SeleniumBasketModalHelper($I);
        $this->basketModelHelper->loadInitialBasketMessageValue();
        $this->basketModelHelper->setBasketMessageValue(SeleniumBasketModalHelper::NEW_BASKET_MESSAGE_NONE);
        parent::_before($I);
    }
    public function _after(AcceptanceTester $I): void
    {
        $this->basketModelHelper->setInitialBasketMessageValue();
        parent::_after($I);
    }
}
