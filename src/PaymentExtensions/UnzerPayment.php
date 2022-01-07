<?php

namespace OxidSolutionCatalysts\Unzer\PaymentExtensions;

use Exception;
use OxidSolutionCatalysts\Unzer\Service\Unzer as UnzerService;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Unzer;

abstract class UnzerPayment
{
    public const CONTROLLER_URL = "order";
    public const RETURN_CONTROLLER_URL = "order";
    public const FAILURE_URL = "";
    public const PENDING_URL = "order&fnc=unzerExecuteAfterRedirect&uzrredirect=1";
    public const SUCCESS_URL = "thankyou";

    /** @var Unzer */
    protected $unzerSDK;

    /** @var UnzerService */
    protected $unzerService;

    /** @var string */
    protected $unzerOrderId;

    /** @var string */
    protected $paymentMethod;

    /** @var array */
    protected $allowedCurrencies = [];

    public function __construct(
        Unzer $unzerSDK,
        UnzerService $unzerService
    ) {
        $this->unzerSDK = $unzerSDK;
        $this->unzerService = $unzerService;

        $this->unzerOrderId = 'o' . str_replace(['0.', ' '], '', microtime(false));
    }

    public function getPaymentCurrencies(): array
    {
        return $this->allowedCurrencies;
    }

    abstract public function getUnzerPaymentTypeObject();

    /**
     * @throws UnzerApiException
     * @throws Exception
     */
    public function execute(
        \OxidEsales\Eshop\Application\Model\User $userModel,
        \OxidEsales\Eshop\Application\Model\Basket $basketModel
    ): bool {
        $paymentType = $this->getUnzerPaymentTypeObject();

        $customer = $this->unzerService->getUnzerCustomer($userModel);

        $paymentProcedure = $this->unzerService->getPaymentProcedure($this->paymentMethod);

        $transaction = $paymentType->{$paymentProcedure}(
            $basketModel->getPrice()->getPrice(),
            $basketModel->getBasketCurrency()->name,
            $this->unzerService->prepareRedirectUrl(self::PENDING_URL, true),
            $customer,
            $this->unzerOrderId,
            $this->unzerService->getShopMetadata($this->paymentMethod)
        );

        $this->unzerService->setSessionVars($transaction);

        return true;
    }
}
