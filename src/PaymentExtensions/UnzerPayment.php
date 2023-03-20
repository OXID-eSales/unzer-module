<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\PaymentExtensions;

use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Service\Unzer as UnzerService;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Unzer;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
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

    /** @var bool */
    protected $ajaxResponse = false;

    /** @var array */
    protected $allowedCurrencies = [];

    public function __construct(
        Unzer $unzerSDK,
        UnzerService $unzerService
    ) {
        $this->unzerSDK = $unzerSDK;
        $this->unzerService = $unzerService;

        $this->unzerOrderId = $this->unzerService->generateUnzerOrderId();

        $this->unzerService->setIsAjaxPayment($this->ajaxResponse);
    }

    /**
     * @return array
     */
    public function getPaymentCurrencies(): array
    {
        return $this->allowedCurrencies;
    }

    /**
     * @return bool
     */
    public function redirectUrlNeedPending(): bool
    {
        return $this->needPending;
    }

    /**
     * @return BasePaymentType
     */
    abstract public function getUnzerPaymentTypeObject(): BasePaymentType;

    /**
     * @param User $userModel
     * @param Basket $basketModel
     * @return bool
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function execute(
        User $userModel,
        Basket $basketModel
    ): bool {
        $request = Registry::getRequest();
        $paymentType = $this->getUnzerPaymentTypeObject();

        /** @var string $unzerComSector */
        $unzerComSector = $request->getRequestParameter('unzer_commercial_sector', '');
        /** @var string $unzerComRegNumber */
        $unzerComRegNumber = $request->getRequestParameter('unzer_commercial_register_number', '');
        $customer = $this->unzerService->getUnzerCustomer(
            $userModel,
            null,
            $unzerComSector,
            $unzerComRegNumber
        );

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

        if ($request->getRequestParameter('birthdate')) {
            $userModel->save();
        }

        return true;
    }
}
