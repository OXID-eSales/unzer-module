<?php

namespace OxidSolutionCatalysts\Unzer\PaymentExtensions;

use Exception;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Service\Unzer as UnzerService;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Unzer;

abstract class UnzerPayment
{
    /** @var Unzer */
    protected $unzerSDK;

    /** @var UnzerService */
    protected $unzerService;

    /** @var string */
    protected $unzerOrderId;

    /** @var string */
    protected $paymentMethod = '';

    /** @var bool */
    protected $needPending = false;

    /** @var array */
    protected $allowedCurrencies = [];

    public function __construct(
        Unzer $unzerSDK,
        UnzerService $unzerService
    ) {
        $this->unzerSDK = $unzerSDK;
        $this->unzerService = $unzerService;

        $this->unzerOrderId = $this->unzerService->generateUnzerOrderId();
    }

    public function getPaymentCurrencies(): array
    {
        return $this->allowedCurrencies;
    }

    public function redirectUrlNeedPending(): bool
    {
        return $this->needPending;
    }

    abstract public function getUnzerPaymentTypeObject(): BasePaymentType;

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
        $uzrBasket = $this->unzerService->getUnzerBasket($this->unzerOrderId, $basketModel);

        $transaction = $paymentType->{$paymentProcedure}(
            $basketModel->getPrice()->getPrice(),
            $basketModel->getBasketCurrency()->name,
            $this->unzerService->prepareOrderRedirectUrl($this->redirectUrlNeedPending()),
            $customer,
            $this->unzerOrderId,
            $this->unzerService->getShopMetadata($this->paymentMethod),
            $uzrBasket
        );

        $this->unzerService->setSessionVars($transaction);

        if (Registry::getRequest()->getRequestParameter('birthdate')) {
            $userModel->save();
        }

        return true;
    }
}
