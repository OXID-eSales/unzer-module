<?php //phpcs:ignoreFile

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Core;

class ShopControl_parent extends \OxidEsales\Eshop\Core\ShopControl
{
}

class Config_parent extends \OxidEsales\Eshop\Core\Config
{
}

class ViewConfig_parent extends \OxidEsales\Eshop\Core\ViewConfig
{
}


namespace OxidSolutionCatalysts\Unzer\Model;

class PaymentGateway_parent extends \OxidEsales\Eshop\Application\Model\PaymentGateway
{
}

class Payment_parent extends \OxidEsales\Eshop\Application\Model\Payment
{
}

class Order_parent extends \OxidEsales\Eshop\Application\Model\Order
{
}


namespace OxidSolutionCatalysts\Unzer\Controller;

class PaymentController_parent extends \OxidEsales\Eshop\Application\Controller\PaymentController
{
}

class OrderController_parent extends \OxidEsales\Eshop\Application\Controller\OrderController
{
}


namespace OxidSolutionCatalysts\Unzer\Controller\Admin;

class ModuleConfiguration_parent extends \OxidEsales\Eshop\Application\Controller\Admin\ModuleConfiguration
{
}


namespace OxidEsales\Eshop\Core;

class Language extends \OxidEsales\EshopCommunity\Core\Language
{
    public function translateString($sStringToTranslate, $iLang = null, $blAdminMode = null): string
    {
    }
}
