<?php

namespace OxidSolutionCatalysts\Unzer\Core;

use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Exception\Redirect;
use OxidSolutionCatalysts\Unzer\Exception\UnzerException;

/**
 * @mixin \OxidEsales\Eshop\Core\ShopControl
 */
class ShopControl extends ShopControl_parent
{
    /**
     * @param \OxidEsales\Eshop\Core\Exception\StandardException $exception
     */
    protected function _handleBaseException($exception): void // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        if ($exception instanceof UnzerException) {
            $this->handleCustomUnzerException($exception);
        } else {
            parent::_handleBaseException($exception);
        }
    } // @codeCoverageIgnore

    public function handleCustomUnzerException(UnzerException $exception): void
    {
        if ($exception instanceof Redirect) {
            $this->handleRedirectException($exception);
        } else {
            parent::_handleBaseException($exception);
        }
    } // @codeCoverageIgnore

    protected function handleRedirectException(Redirect $redirectException): void
    {
        Registry::getUtils()->redirect($redirectException->getDestination());
    }
}
