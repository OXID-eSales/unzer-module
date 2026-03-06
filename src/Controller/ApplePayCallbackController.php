<?php

namespace OxidSolutionCatalysts\Unzer\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Traits\Request;
use OxidSolutionCatalysts\Unzer\Service\ApplePaySessionHandler;
use OxidSolutionCatalysts\Unzer\Service\ResponseHandler;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;

class ApplePayCallbackController extends FrontendController
{
    use ServiceContainer;
    use Request;

    /** @var string[] */
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

    /**
     * @throws \JsonException
     */
    public function validateMerchant(): void
    {
        $merchValidUrl = $this->getUnzerStringRequestParameter('merchantValidationUrl');

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
