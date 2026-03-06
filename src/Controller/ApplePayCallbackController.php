<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Controller;

use JsonException;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Service\ApplePaySessionHandler;
use OxidSolutionCatalysts\Unzer\Service\DebugHandler;
use OxidSolutionCatalysts\Unzer\Service\ResponseHandler;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;

class ApplePayCallbackController extends FrontendController
{
    use ServiceContainer;

    private const ALLOWED_APPLE_PAY_HOSTS = [
        'apple-pay-gateway.apple.com',
        'apple-pay-gateway-nc-pod1.apple.com',
        'apple-pay-gateway-nc-pod2.apple.com',
        'apple-pay-gateway-nc-pod3.apple.com',
        'apple-pay-gateway-nc-pod4.apple.com',
        'apple-pay-gateway-nc-pod5.apple.com',
        'apple-pay-gateway-pr-pod1.apple.com',
        'apple-pay-gateway-pr-pod2.apple.com',
        'apple-pay-gateway-pr-pod3.apple.com',
        'apple-pay-gateway-pr-pod4.apple.com',
        'apple-pay-gateway-pr-pod5.apple.com',
        'cn-apple-pay-gateway.apple.com',
    ];

    public function validateMerchant(): void
    {
        /** @var string $merchValidUrl */
        $merchValidUrl = Registry::getRequest()->getRequestParameter('merchantValidationUrl');
        $merchValidUrl = is_string($merchValidUrl) ? $merchValidUrl : '';

        $responseHandler = $this->getServiceFromContainer(ResponseHandler::class);

        $parsed = parse_url($merchValidUrl);
        if (
            !is_array($parsed)
            || ($parsed['scheme'] ?? '') !== 'https'
            || !in_array($parsed['host'] ?? '', self::ALLOWED_APPLE_PAY_HOSTS, true)
        ) {
            $responseHandler
                ->response()
                ->setUnauthorized()
                ->sendJson();
            return;
        }

        $validationResponse = $this
            ->getServiceFromContainer(ApplePaySessionHandler::class)
            ->validateMerchant($merchValidUrl);

        if (is_array($validationResponse)) {
            $responseHandler
                ->response()
                ->setData(['validationResponse' => $validationResponse])
                ->sendJson();
        }

        $responseHandler
            ->response()
            ->setUnauthorized()
            ->sendJson();
    }
}
