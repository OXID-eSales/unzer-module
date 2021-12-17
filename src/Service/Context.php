<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContext;
use Webmozart\PathUtil\Path;

class Context extends BasicContext
{
    /** @var Config */
    protected $shopConfig;

    public function __construct(Config $shopConfig)
    {
        $this->shopConfig = $shopConfig;
    }

    public function getUnzerLogFilePath(): string
    {
        return Path::join([
            $this->shopConfig->getLogsDir(),
            'unzer',
            $this->getUnzerLogFileName()
        ]);
    }

    private function getUnzerLogFileName(): string
    {
        return "unzer_" . date("Y-m-d") . ".log";
    }

    public function getCurrentShopId(): int
    {
        return $this->shopConfig->getShopId();
    }

    public function getActiveCurrencyName(): string
    {
        return $this->shopConfig->getActShopCurrencyObject()->name;
    }
}
