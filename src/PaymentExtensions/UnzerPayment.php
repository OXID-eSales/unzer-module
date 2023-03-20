<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\PaymentExtensions;

use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Service\Unzer as UnzerService;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Resources\TransactionTypes\Authorization;
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
        $companyType = strval($request->getRequestParameter('unzer_company_form', ''));

        $customer = $this->unzerService->getUnzerCustomer(
            $userModel,
            null,
            $companyType
        );

        $paymentProcedure = $this->unzerService->getPaymentProcedure($this->paymentMethod);
        $uzrBasket = $this->unzerService->getUnzerBasket($this->unzerOrderId, $basketModel);

        if (!method_exists($paymentType, $paymentProcedure)) {
            // paylater invoice type id - we need it later on I guess
            $typeId = $request->getRequestParameter('unzer_type_id');
            $customerObj = $this->unzerSDK->createCustomer($customer);
            $basketObj = $this->unzerSDK->createBasket($uzrBasket);

            $authObj = new Authorization(
                $basketModel->getPrice()->getBruttoPrice(),
                $basketModel->getBasketCurrency()->name,
                $this->unzerService->prepareOrderRedirectUrl($this->redirectUrlNeedPending())
            );
            $performAuth = $this->unzerSDK->performAuthorization(
                $authObj,
                $paymentType,
                $customerObj,
                null,
                $basketObj
            );

            $message = sprintf('Procedure "%s" not found for "%s"', $paymentProcedure, $this->paymentMethod);
            Registry::getUtilsView()->addErrorToDisplay(new StandardException($message));
            throw new UnzerApiException($message);
        }

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
