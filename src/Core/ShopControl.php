<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Core;

use OxidEsales\Eshop\Core\DisplayError;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Exception\Redirect;
use OxidSolutionCatalysts\Unzer\Exception\RedirectWithMessage;
use OxidSolutionCatalysts\Unzer\Exception\UnzerException;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;

/**
 * @mixin \OxidEsales\Eshop\Core\ShopControl
 */
class ShopControl extends ShopControl_parent
{
    use ServiceContainer;

    /**
     * @param StandardException $exception
     */
    protected function _handleBaseException($exception): void // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        if ($exception instanceof UnzerException) {
            $this->handleCustomUnzerException($exception);
            return;
        }

        parent::_handleBaseException($exception);
    }

    /**
     * @param UnzerException $exception
     */
    public function handleCustomUnzerException(UnzerException $exception): void
    {
        if ($exception instanceof RedirectWithMessage) {
            $this->handleRedirectWithMessageException($exception);
            return;
        }

        if ($exception instanceof Redirect) {
            $this->handleRedirectException($exception, false);
            return;
        }

        parent::_handleBaseException($exception);
    }

    /**
     * @param Redirect $redirectException
     * @param bool $blAddRedirectParam
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    protected function handleRedirectException(Redirect $redirectException, bool $blAddRedirectParam = true): void
    {
        Registry::getUtils()->redirect($redirectException->getDestination(), $blAddRedirectParam);
    }

    /**
     * @param RedirectWithMessage $redirectException
     */
    protected function handleRedirectWithMessageException(RedirectWithMessage $redirectException): void
    {
        $displayError = oxNew(DisplayError::class);
        $displayError->setMessage($redirectException->getMessageKey());
        $displayError->setFormatParameters($redirectException->getMessageParams());

        Registry::getUtilsView()->addErrorToDisplay($displayError);

        $this->handleRedirectException($redirectException);
    }
}
