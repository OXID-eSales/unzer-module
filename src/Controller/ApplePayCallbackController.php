<?php

namespace OxidSolutionCatalysts\Unzer\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Exception\Redirect;
use OxidSolutionCatalysts\Unzer\Exception\RedirectWithMessage;
use OxidSolutionCatalysts\Unzer\Service\ApplePaySessionHandler;
use OxidSolutionCatalysts\Unzer\Service\Payment as PaymentService;
use OxidSolutionCatalysts\Unzer\Service\ResponseHandler;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;

class ApplePayCallbackController extends FrontendController
{
    use ServiceContainer;

    /**
     * @throws \JsonException
     */
    public function validateMerchant(): void
    {
        $merchantValidationUrl = urldecode(Registry::getRequest()->getRequestEscapedParameter('merchantValidationUrl'));

        $responseHandler = $this->getServiceFromContainer(ResponseHandler::class);

        if ($validationResponse = $this->getServiceFromContainer(ApplePaySessionHandler::class)->validateMerchant($merchantValidationUrl)) {
            $responseHandler->response()->setData(['validationResponse' => $validationResponse])->sendJson();
        }

        $responseHandler->response()->setUnauthorized()->sendJson();
    }
}