<?php

require_once __DIR__ . '/Unit/Service/Mocks/MockOrder.php';
require_once __DIR__ . '/Unit/Service/Mocks/MockExtendedOrder.php';

use OxidSolutionCatalysts\Unzer\Tests\Unit\Service\Mocks\MockOrder;
use OxidSolutionCatalysts\Unzer\Tests\Unit\Service\Mocks\MockExtendedOrder;

class_alias(MockOrder::class, 'OxidEsales\Eshop\Application\Model\Order');
class_alias(MockExtendedOrder::class, 'OxidSolutionCatalysts\Unzer\Model\Order');
