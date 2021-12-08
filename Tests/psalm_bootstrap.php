<?php

declare(strict_types=1);

require __DIR__ . "/../source/bootstrap.php";

class_alias(
    \OxidEsales\Eshop\Application\Controller\OrderController::class,
    \OxidSolutionCatalysts\Unzer\Controller\OrderController_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Model\PaymentGateway::class,
    OxidSolutionCatalysts\Unzer\Model\PaymentGateway_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Model\Payment::class,
    \OxidSolutionCatalysts\Unzer\Model\Payment_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Model\Order::class,
    \OxidSolutionCatalysts\Unzer\Model\Order_parent::class
);

class_alias(
    \OxidEsales\Eshop\Core\ViewConfig::class,
    \OxidSolutionCatalysts\Unzer\Core\ViewConfig_parent::class
);

class_alias(
    \OxidEsales\Eshop\Core\Config::class,
    \OxidSolutionCatalysts\Unzer\Core\Config_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Controller\PaymentController::class,
    \OxidSolutionCatalysts\Unzer\Controller\PaymentController_parent::class
);
