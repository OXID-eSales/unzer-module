<?php

namespace OxidSolutionCatalysts\Unzer\Core;

use OxidSolutionCatalysts\Unzer\Exception\UnzerException;

/**
 * @mixin \OxidEsales\Eshop\Core\ShopControl
 */
class ShopControl extends ShopControl_parent
{
    /**
     * @param \OxidEsales\Eshop\Core\Exception\StandardException $exception
     */
    protected function _handleBaseException($exception) // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        if ($exception instanceof UnzerException) {
            $this->handleCustomUnzerException($exception);
        } else {
            parent::_handleBaseException($exception);
        }
    }

    public function handleCustomUnzerException($exception)
    {
        throw $exception;
    }
}
