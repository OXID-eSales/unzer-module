<?php

namespace OxidSolutionCatalysts\Unzer\Controller;

use JsonException;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Service\ApplePaySessionHandler;
use OxidSolutionCatalysts\Unzer\Service\ResponseHandler;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;

class ApplePayCallbackController extends FrontendController
{
    use ServiceContainer;

    /**
     * @throws JsonException
     */
    public function validateMerchant(): void
    {
        /** @var string $merchValidUrlRaw */
        $merchValidUrlRaw = Registry::getRequest()->getRequestEscapedParameter('merchantValidationUrl');
        $merchValidUrl = urldecode($merchValidUrlRaw);

        $responseHandler = $this->getServiceFromContainer(ResponseHandler::class);
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
