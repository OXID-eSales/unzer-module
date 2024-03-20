<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\PaymentExtensions;

use Exception;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidSolutionCatalysts\Unzer\Service\Payment;
use OxidSolutionCatalysts\Unzer\Service\Transaction;
use OxidSolutionCatalysts\Unzer\Service\DebugHandler;
use OxidSolutionCatalysts\Unzer\Service\Unzer as UnzerService;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Resources\PaymentTypes\Paypal as PayPalPaymentType;
use UnzerSDK\Resources\PaymentTypes\PaylaterInstallment;
use UnzerSDK\Resources\TransactionTypes\AbstractTransactionType;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Unzer;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class UnzerPayment implements UnzerPaymentInterface
{
    use ServiceContainer;

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

    private DebugHandler $logger;

    /**
     * @throws Exception
     */
    public function __construct(
        Unzer $unzerSDK,
        UnzerService $unzerService,
        DebugHandler $logger
    ) {
        $this->unzerSDK = $unzerSDK;
        $this->unzerService = $unzerService;

        $this->unzerOrderId = (string)$this->unzerService->generateUnzerOrderId();

        $this->unzerService->setIsAjaxPayment($this->ajaxResponse);
        $this->logger = $logger;
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
     * @throws \JsonException
     * @throws \OxidSolutionCatalysts\Unzer\Exception\UnzerException
     * @throws \UnzerSDK\Exceptions\UnzerApiException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function execute(
        User $userModel,
        Basket $basketModel
    ): bool {
        $request = Registry::getRequest();
        $paymentType = $this->getUnzerPaymentTypeObject();
        if ($paymentType instanceof PayPalPaymentType) {
            $paymentData = $request->getRequestParameter('paymentData');
            $paymentData = is_string($paymentData) ? $paymentData : '';
            $aPaymentData = json_decode($paymentData, true, 512, JSON_THROW_ON_ERROR);
            if (is_array($aPaymentData) && isset($aPaymentData['id'])) {
                $paymentType->setId($aPaymentData['id']);
            }
        }
        /** @var string $companyType */
        $companyType = $request->getRequestParameter('unzer_company_form', '');

        $customer = $this->unzerService->getUnzerCustomer(
            $userModel,
            null,
            $companyType
        );

        // first try to fetch customer, secondly create anew if not found in unzer
        try {
            $customer = $this->unzerSDK->fetchCustomer($customer);
            // for comparison and update, the original object must be recreated
            $originalCustomer = $this->unzerService->getUnzerCustomer(
                $userModel,
                null,
                $companyType
            );
            if ($this->unzerService->updateUnzerCustomer($customer, $originalCustomer)) {
                $customer = $this->unzerSDK->updateCustomer($customer);
            }
        } catch (UnzerApiException $apiException) {
            $customer = $this->unzerSDK->createCustomer($customer);
        }

        $transaction = $this->doTransactions($basketModel, $customer, $userModel, $paymentType);
        $this->unzerService->setSessionVars($transaction);

        if ($request->getRequestParameter('birthdate')) {
            $userModel->save();
        }

        $savePayment = Registry::getRequest()->getRequestParameter('oscunzersavepayment');

        if ($savePayment === "1" && $userModel->getId()) {
            $transactionService = $this->getServiceFromContainer(Transaction::class);
            $payment = $this->getServiceFromContainer(Payment::class)->getSessionUnzerPayment();
            try {
                $transactionService->writeTransactionToDB(
                    Registry::getSession()->getSessionChallengeToken(),
                    $userModel->getId(),
                    $payment
                );
            } catch (Exception $e) {
                $this->logger
                    ->log('Could not save Transaction for PaymentID (savePayment): ' . $e->getMessage());
            }
        }

        return true;
    }

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    protected function doTransactions(
        Basket $basketModel,
        Customer $customer,
        User $userModel,
        BasePaymentType $paymentType
    ): AbstractTransactionType {
        $this->throwExceptionIfPaymentDataError();
        $paymentProcedure = $this->unzerService->getPaymentProcedure($this->paymentMethod);
        $uzrBasket = $this->unzerService->getUnzerBasket($this->unzerOrderId, $basketModel);
        /** @var $paymentType PaylaterInstallment */
        if ($paymentType instanceof PaylaterInstallment) {
            $auth = oxNew(Authorization::class);
            $auth->setAmount($basketModel->getPrice()->getPrice());
            $currency = $basketModel->getBasketCurrency();
            $auth->setCurrency($currency->name);
            $auth->setReturnUrl($this->unzerService->prepareOrderRedirectUrl($this->redirectUrlNeedPending()));
            $auth->setOrderId($this->unzerOrderId);

            $uzrRiskData = $this->unzerService->getUnzerRiskData(
                $customer,
                $userModel
            );
            $auth->setRiskData($uzrRiskData);
            try {
                $loader = $this->getServiceFromContainer(UnzerSDKLoader::class);
                $UnzerSdk = $loader->getUnzerSDK('B2C', $currency->name, true);
                $transaction = $UnzerSdk->performAuthorization(
                    $auth,
                    $paymentType,
                    $customer,
                    $this->unzerService->getShopMetadata($this->paymentMethod),
                    $uzrBasket
                );
            } catch (UnzerApiException $e) {
                throw new UnzerApiException($e->getMerchantMessage(), $e->getClientMessage());
            }
            return $transaction;
        }

        return $paymentType->{$paymentProcedure}(
            $basketModel->getPrice()->getPrice(),
            $basketModel->getBasketCurrency()->name,
            $this->unzerService->prepareOrderRedirectUrl($this->redirectUrlNeedPending()),
            $customer,
            $this->unzerOrderId,
            $this->unzerService->getShopMetadata($this->paymentMethod),
            $uzrBasket
        );
    }

    /**
     * proves if there are errors coming from unzer like: credit card has been expired, to send this error to the
     * customer and log it
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Exception
     */
    private function throwExceptionIfPaymentDataError(): void
    {
        /** @var UnzerService $unzerService */
        $unzerService = ContainerFactory::getInstance()->getContainer()->get(UnzerService::class);
        $unzerPaymentData = $unzerService->getUnzerPaymentDataFromRequest();

        if ($unzerPaymentData->isSuccess === false && $unzerPaymentData->isError === true) {
            $this->logger
                ->log(
                    sprintf(
                        'Could not process Transaction for '
                            . 'paymentId %s, traceId: %s, timestamp: %s, customerMessage: %s',
                        $unzerPaymentData->id,
                        $unzerPaymentData->traceId,
                        $unzerPaymentData->timestamp,
                        $unzerPaymentData->getFirstErrorMessage()
                    )
                );
            $exceptionMessage = $unzerPaymentData->getFirstErrorMessage();
            if ($exceptionMessage === null) {
                $exceptionMessage = '';
            }
            throw new Exception($exceptionMessage);
        }
    }
}
