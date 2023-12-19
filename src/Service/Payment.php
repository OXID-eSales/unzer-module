<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Service;

use Exception;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\Payment as PaymentModel;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\Session;
use OxidSolutionCatalysts\Unzer\Core\UnzerDefinitions;
use OxidSolutionCatalysts\Unzer\Exception\Redirect;
use OxidSolutionCatalysts\Unzer\Exception\RedirectWithMessage;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\UnzerPayment as AbstractUnzerPayment;
use OxidSolutionCatalysts\Unzer\Service\Transaction as TransactionService;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\AbstractUnzerResource;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Resources\PaymentTypes\InstallmentSecured;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Resources\TransactionTypes\Cancellation;
use UnzerSDK\Resources\TransactionTypes\Shipment;

/**
 * TODO: Decrease count of dependencies to 13
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * TODO: Decrease overall complexity below 50
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Payment
{
    private const STATUS_OK = "OK";
    private const STATUS_NOT_FINISHED = "NOT_FINISHED";
    private const STATUS_ERROR = "ERROR";

    /** @var Session */
    protected $session;

    /** @var PaymentExtensionLoader */
    protected $paymentExtLoader;

    /** @var Translator */
    protected $translator;

    /** @var Unzer */
    protected $unzerService;

    /** @var UnzerSDKLoader */
    protected $unzerSDKLoader;

    /**
     * @var string
     */
    protected $redirectUrl;

    /**
     * @var string
     */
    protected $pdfLink;

    /** @var TransactionService */
    protected $transactionService;

    /**
     * @param Session $session
     * @param PaymentExtensionLoader $paymentExtLoader
     * @param Translator $translator
     * @param Unzer $unzerService
     * @param UnzerSDKLoader $unzerSDKLoader
     * @param TransactionService $transactionService
     */
    public function __construct(
        Session $session,
        PaymentExtensionLoader $paymentExtLoader,
        Translator $translator,
        Unzer $unzerService,
        UnzerSDKLoader $unzerSDKLoader,
        TransactionService $transactionService
    ) {
        $this->session = $session;
        $this->paymentExtLoader = $paymentExtLoader;
        $this->translator = $translator;
        $this->unzerService = $unzerService;
        $this->unzerSDKLoader = $unzerSDKLoader;
        $this->transactionService = $transactionService;
    }

    /**
     * @throws Redirect
     * @throws RedirectWithMessage
     */
    public function executeUnzerPayment(PaymentModel $paymentModel): bool
    {
        try {
            /** @var string $customerType */
            $customerType = Registry::getRequest()->getRequestParameter('unzer_customer_type', '');
            $user = $this->session->getUser();
            $basket = $this->session->getBasket();
            $currency = $basket->getBasketCurrency()->name;

            /** @var AbstractUnzerPayment $paymentExtension */
            $paymentExtension = $this->paymentExtLoader->getPaymentExtensionByCustomerTypeAndCurrency(
                $paymentModel,
                $customerType,
                $currency
            );
            $paymentExtension->execute(
                $user,
                $basket
            );

            /** @var string $sess_challenge */
            $sess_challenge = $this->session->getVariable('sess_challenge');
            $this->transactionService->writeTransactionToDB(
                $sess_challenge,
                $this->session->getUser()->getId(),
                $this->getSessionUnzerPayment($paymentExtension, $currency)
            );

            $paymentStatus = $this->getUnzerPaymentStatus() !== self::STATUS_ERROR;

            if ($this->redirectUrl) {
                throw new Redirect($this->redirectUrl);
            }

            if ($this->pdfLink) {
                throw new Redirect($this->unzerService->preparePdfConfirmRedirectUrl());
            }
        } catch (Redirect $e) {
            throw $e;
        } catch (UnzerApiException $e) {
            $this->removeTemporaryOrder();

            throw new RedirectWithMessage(
                $this->unzerService->prepareOrderRedirectUrl(
                    $paymentExtension instanceof AbstractUnzerPayment && $paymentExtension->redirectUrlNeedPending()
                ),
                $this->translator->translateCode($e->getErrorId(), $e->getClientMessage())
            );
        } catch (Exception $e) {
            $this->removeTemporaryOrder();

            throw new RedirectWithMessage(
                $this->unzerService->prepareOrderRedirectUrl(
                    $paymentExtension instanceof AbstractUnzerPayment && $paymentExtension->redirectUrlNeedPending()
                ),
                $e->getMessage()
            );
        }

        return $paymentStatus;
    }

    /**
     * @return bool
     */
    public function removeTemporaryOrder(): bool
    {
        $orderModel = oxNew(Order::class);
        /** @var string $sessionOrderId */
        $sessionOrderId = $this->session->getVariable('sess_challenge');
        return $orderModel->delete($sessionOrderId);
    }

    /**
     * @return string
     * @throws UnzerApiException
     */
    public function getUnzerPaymentStatus(): string
    {
        $result = self::STATUS_ERROR;

        /** @var \UnzerSDK\Resources\Payment $sessionUnzerPayment */
        $sessionUnzerPayment = $this->getSessionUnzerPayment();
        $transaction = $sessionUnzerPayment->getInitialTransaction();

        if ($sessionUnzerPayment->isCompleted()) {
            $result = self::STATUS_OK;
        } elseif ($sessionUnzerPayment->isPending() && $transaction) {
            if ($transaction->isSuccess()) {
                if ($transaction instanceof Authorization) {
                    /** @var string $pdfLink */
                    $pdfLink = $transaction->getPDFLink();
                    $this->pdfLink = $pdfLink;
                }
                if ($this->isPdfSession()) {
                    $this->pdfLink = '';
                }
                $result = self::STATUS_OK;
            } elseif ($transaction->isPending()) {
                $result = self::STATUS_NOT_FINISHED;
                /** @var string $redirectUrl */
                $redirectUrl = $transaction->getRedirectUrl();
                $this->redirectUrl = $redirectUrl;
            } elseif ($transaction->isError()) {
                throw new Exception($this->translator->translateCode(
                    $transaction->getMessage()->getCode(),
                    "Error in transaction for customer " . $transaction->getMessage()->getCustomer()
                ));
            }
        }

        return $result;
    }

    /**
     * @return int
     * @throws UnzerApiException
     */
    public function getUnzerOrderId(): int
    {
        $result = 0;
        $sessionUnzerPayment = $this->getSessionUnzerPayment();
        if ($sessionUnzerPayment) {
            $transaction = $sessionUnzerPayment->getInitialTransaction();
            if ($transaction) {
                $result = (int)$transaction->getOrderId();
            }
        }
        return $result;
    }

    /**
     * @param string $customerType
     * @param string $currency
     * @return \UnzerSDK\Unzer
     */
    public function getUnzerSDK(string $customerType = '', string $currency = ''): \UnzerSDK\Unzer
    {
        return $this->unzerSDKLoader->getUnzerSDK($customerType, $currency);
    }

    /**
     * @return \UnzerSDK\Resources\Payment|null
     * @throws UnzerApiException
     */
    public function getSessionUnzerPayment($paymentExtension = null, $currency = null): ?\UnzerSDK\Resources\Payment
    {
        $paymentId = $this->session->getVariable('PaymentId');
        if (is_string($paymentId)) {
            /** @var string $sessionOrderId */
            $sessionOrderId = $this->session->getVariable('sess_challenge');
            /** @var Order $order */
            $order = oxNew(Order::class);
            $order->load($sessionOrderId);
            $customerType = '';
            if ($currency === null) {
                /** @var string $currency */
                $currency = $order->getFieldData('oxcurrency') ?? '';
            }

            if ($paymentExtension !== null && $currency !== null) {
                $oRequest = oxNew(Request::class);
                if ($paymentExtension instanceof \OxidSolutionCatalysts\Unzer\PaymentExtensions\Invoice) {
                    if ($oRequest->getRequestParameter('unzer_customer_type') === 'B2C') {
                        $customerType = 'B2C';
                    } else {
                        $customerType = 'B2B';
                    }
                }
            }

            if ($order->getFieldData('oxpaymenttype') == UnzerDefinitions::INVOICE_UNZER_PAYMENT_ID) {
                $customerType = 'B2C';
                if (!empty($order->getFieldData('oxbillcompany')) || !empty($order->getFieldData('oxdelcompany'))) {
                    $customerType = 'B2B';
                }
            }
            $sdk = $this->unzerSDKLoader->getUnzerSDK($customerType, $currency);
            return $sdk->fetchPayment($paymentId);
        }

        return null;
    }

    /**
     * @param Order $oOrder
     * @param string $unzerid
     * @param string $chargeid
     * @param float $amount
     * @param string $reason
     * @return UnzerApiException|bool
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function doUnzerCancel($oOrder, $unzerid, $chargeid, $amount, $reason)
    {
        try {
            $sdk = $this->unzerSDKLoader->getUnzerSDKbyPaymentType($unzerid);

            if ($chargeid) {
                $unzerCharge = $sdk->fetchChargeById($unzerid, $chargeid);
                $cancellation = $unzerCharge->cancel($amount, $reason);
            } else {
                $payment = $sdk->fetchPayment($unzerid);
                $cancellation = new Cancellation($amount);
                $cancellation = $sdk->cancelChargedPayment($payment, $cancellation);
            }

            /** @var string $oxuserid */
            $oxuserid = $oOrder->getFieldData('oxuserid');
            $this->transactionService->writeCancellationToDB(
                $oOrder->getId(),
                $oxuserid,
                $cancellation
            );
        } catch (UnzerApiException $e) {
            return $e;
        }
        return true;
    }

    /**
     * @param \OxidSolutionCatalysts\Unzer\Model\Order|null $oOrder
     * @param string $unzerid
     * @param float $amount
     * @return UnzerApiException|bool
     */
    public function doUnzerCollect($oOrder, $unzerid, $amount)
    {
        if (!($oOrder instanceof Order)) {
            return false;
        }

        try {
            $unzerPayment = $this->unzerSDKLoader->getUnzerSDKbyPaymentType($unzerid)->fetchPayment($unzerid);

            /** @var Authorization $authorization */
            $authorization = $unzerPayment->getAuthorization();
            if (null == $authorization) {
                return false;
            }
            $charge = $authorization->charge($amount);

            /** @var string $oxuserid */
            $oxuserid = $oOrder->getFieldData('oxuserid');
            $this->transactionService->writeChargeToDB(
                $oOrder->getId(),
                $oxuserid,
                $charge
            );
            $payment = $charge->getPayment();
            if (
                ($charge->isSuccess()) &&
                ($payment instanceof \UnzerSDK\Resources\Payment) &&
                $payment->getAmount()->getRemaining() === 0.0
            ) {
                $oOrder->markUnzerOrderAsPaid();
            }
        } catch (UnzerApiException $e) {
            return $e;
        }

        return true;
    }

    /**
     * @param \OxidSolutionCatalysts\Unzer\Model\Order|null $oOrder
     * @param string $unzerid
     * @param float $amount
     * @return UnzerApiException|bool
     */
    public function doUnzerAuthorizationCancel($oOrder, $unzerid, $amount)
    {
        if (!($oOrder instanceof Order)) {
            return false;
        }

        try {
            $sdk = $this->unzerSDKLoader->getUnzerSDKbyPaymentType($unzerid);
            $unzerPayment = $sdk->fetchPayment($unzerid);
            $cancellation = new Cancellation($amount);
            $sdk->cancelAuthorizedPayment($unzerPayment, $cancellation);

            /** @var string $oxuserid */
            $oxuserid = $oOrder->getFieldData('oxuserid');
            $this->transactionService->writeTransactionToDB(
                $oOrder->getId(),
                $oxuserid,
                $unzerPayment
            );
        } catch (UnzerApiException $e) {
            return $e;
        }

        return true;
    }

    /**
     * @param \OxidSolutionCatalysts\Unzer\Model\Order|null $oOrder
     * @param string $sPaymentId
     * @return UnzerApiException|bool
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function sendShipmentNotification($oOrder, $sPaymentId = null)
    {
        if (!($oOrder instanceof Order)) {
            return false;
        }

        $sPaymentId = $sPaymentId ?? $this->transactionService->getPaymentIdByOrderId($oOrder->getId());
        $transactionDetails = $this->transactionService->getCustomerTypeAndCurrencyByOrderId($oOrder->getId());

        $blSuccess = false;

        if ($sPaymentId) {
            $sdk = $this->getUnzerSDK($transactionDetails['customertype'], $transactionDetails['currency']);
            /** @var string $sInvoiceNr */
            $sInvoiceNr = $oOrder->getUnzerInvoiceNr();

            $unzerPayment = $sdk->fetchPayment($sPaymentId);

            if ($unzerPayment->getPaymentType() instanceof InstallmentSecured) {
                $this->setInstallmentDueDate($unzerPayment);
            }
            try {
                $shipment = $sdk->ship(
                    $unzerPayment,
                    $sInvoiceNr,
                    $oOrder->getId()
                );
                $unzerPayment->addShipment($shipment);

                foreach ($unzerPayment->getShipments() as $unzShipment) {
                    if ($unzShipment->isSuccess()) {
                        $blSuccess = true;
                    }
                }
            } catch (UnzerApiException $e) {
                $blSuccess = $e;
            }

            if (!$blSuccess && $unzerPayment->getAmount()->getRemaining() === 0.0) {
                try {
                    /** @var string $oxuserid */
                    $oxuserid = $oOrder->getFieldData('oxuserid');
                    /** @var Shipment $shipment */
                    $shipment = $unzerPayment->ship($sInvoiceNr);
                    $blSuccess = $this->transactionService->writeTransactionToDB(
                        $oOrder->getId(),
                        $oxuserid,
                        $unzerPayment,
                        $shipment
                    );
                } catch (UnzerApiException $e) {
                    $blSuccess = $e;
                }
            }
        }

        return $blSuccess;
    }

    /**
     * @param \UnzerSDK\Resources\Payment $unzerPayment
     * @return BasePaymentType|AbstractUnzerResource The updated PaymentType object.
     */
    public function setInstallmentDueDate($unzerPayment)
    {
        /** @var InstallmentSecured $installment */
        $installment = $unzerPayment->getPaymentType();
        if ($installment->getInvoiceDate() === null) {
            $installment->setInvoiceDate($this->getYesterdaysTimestamp());
        }
        if ($installment->getInvoiceDueDate() === null) {
            $installment->setInvoiceDueDate($this->getTomorrowsTimestamp());
        }

        return $unzerPayment->getUnzerObject()->updatePaymentType($installment);
    }

    public function getYesterdaysTimestamp(): string
    {
        return date('Y-m-d', strtotime("-1 days"));
    }

    public function getTomorrowsTimestamp(): string
    {
        return date('Y-m-d', strtotime("+1 days"));
    }

    /**
     * @return bool
     */
    public function isPdfSession(): bool
    {
        return (bool) Registry::getRequest()->getRequestParameter('pdfConfirm', '0');
    }
}
