<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance\Helper;

use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

class SeleniumBasketModalHelper
{
    public const NEW_BASKET_MESSAGE_CONFIG_NAME = 'iNewBasketItemMessage';
    public const NEW_BASKET_MESSAGE_NONE = 0;
    public const NEW_BASKET_MESSAGE_SHOW_MESSAGE = 1;
    public const NEW_BASKET_MESSAGE_SHOW_MODAL = 2;
    public const NEW_BASKET_MESSAGE_SHOW_BASKET = 3;

    /** @var null|int */
    private $initialBasketMessageValue = null;

    /** @var AcceptanceTester $acceptanceTester */
    private $acceptanceTester;

    /** @var int $shopId */
    private $shopId;

    public function __construct(AcceptanceTester $acceptanceTester, int $shopId = 1)
    {
        $this->acceptanceTester = $acceptanceTester;
        $this->shopId = $shopId;
    }

    public function loadInitialBasketMessageValue(): void
    {
        $this->initialBasketMessageValue = (int) $this->acceptanceTester->grabColumnFromDatabase(
            'oxconfig',
            'OXVARVALUE',
            [
                'OXVARNAME' => self::NEW_BASKET_MESSAGE_CONFIG_NAME,
                'OXSHOPID' => $this->shopId
            ]
        );
    }

    /**
     * @param int $value one of the NEW_BASKET_MESSAGE_* constants
     */
    public function setBasketMessageValue(int $value): void
    {
        $this->acceptanceTester->updateInDatabase(
            'oxconfig',
            ['OXVARVALUE' => $value],
            [
                'OXVARNAME' => self::NEW_BASKET_MESSAGE_CONFIG_NAME,
                'OXSHOPID' => $this->shopId
            ]
        );
    }

    public function setInitialBasketMessageValue(): void
    {
        $this->acceptanceTester->updateInDatabase(
            'oxconfig',
            ['OXVARVALUE' => $this->initialBasketMessageValue],
            [
                'OXVARNAME' => self::NEW_BASKET_MESSAGE_CONFIG_NAME,
                'OXSHOPID' => $this->shopId
            ]
        );
    }
}
