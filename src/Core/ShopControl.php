<?php

namespace OxidSolutionCatalysts\Unzer\Core;

/**
 * @mixin \OxidEsales\Eshop\Core\ShopControl
 */
class ShopControl extends ShopControl_parent
{
    protected function _process(
        $class,
        $function,
        $parameters = null,
        $viewsChain = null
    ) {
        parent::_process($class, $function, $parameters, $viewsChain);
    }
}
