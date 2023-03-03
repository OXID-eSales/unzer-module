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
use OxidEsales\Eshop\Core\Session;
use OxidSolutionCatalysts\Unzer\Exception\Redirect;
use OxidSolutionCatalysts\Unzer\Exception\RedirectWithMessage;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\UnzerPayment as AbstractUnzerPayment;
use OxidSolutionCatalysts\Unzer\Service\Transaction as TransactionService;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\AbstractUnzerResource;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Resources\PaymentTypes\InstallmentSecured;
use UnzerSDK\Resources\TransactionTypes\Authorization;

class Payment
{
    private const STATUS_OK = "OK";
    private const STATUS_NOT_FINISHED = "NOT_FINISHED";
    private const STATUS_ERROR = "ERROR";

    /** @var Session */
    protected $session;

    /** @var PaymentExtensionLoader */
    protected $paymentExtensionLoader;

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
     * @param PaymentExtensionLoader $paymentExtensionLoader
     * @param Translator $translator
     * @param Unzer $unzerService
     * @param UnzerSDKLoader $unzerSDKLoader
     * @param TransactionService $transactionService
     */
    public function __construct(
        Session $session,
        PaymentExtensionLoader $paymentExtensionLoader,
        Translator $translator,
        Unzer $unzerService,
        UnzerSDKLoader $unzerSDKLoader,
        TransactionService $transactionService
    ) {
        $this->session = $session;
        $this->paymentExtensionLoader = $paymentExtensionLoader;
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
            /** @var AbstractUnzerPayment $paymentExtension */
            $paymentExtension = $this->paymentExtensionLoader->getPaymentExtension($paymentModel);
            $paymentExtension->execute(
                $this->session->getUser(),
                $this->session->getBasket()
            );

            /** @var string $sess_challenge */
            $sess_challenge = $this->session->getVariable('sess_challenge');
            $this->transactionService->writeTransactionToDB(
                $sess_challenge,
                $this->session->getUser()->getId(),
                $this->getSessionUnzerPayment()
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
     * @return \UnzerSDK\Unzer
     */
    protected function getUnzerSDK(): \UnzerSDK\Unzer
    {
        return $this->unzerSDKLoader->getUnzerSDK();
    }

    /**
     * @return \UnzerSDK\Resources\Payment|null
     * @throws UnzerApiException
     */
    public function getSessionUnzerPayment(): ?\UnzerSDK\Resources\Payment
    {
        if (/** @var string $paymentId */
            $paymentId = $this->session->getVariable('PaymentId')) {
            return $this->getUnzerSDK()->fetchPayment($paymentId);
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
     */
    public function doUnzerCancel($oOrder, $unzerid, $chargeid, $amount, $reason)
    {
        try {
            $unzerPayment = $this->getUnzerSDK()->fetchChargeById($unzerid, $chargeid);

            $cancellation = $unzerPayment->cancel($amount, $reason);
            $this->transactionService->writeCancellationToDB(
                $oOrder->getId(),
                $oOrder->getFieldData('oxuserid'),
                $cancellation
            );
        } catch (UnzerApiException $e) {
            return $e;
        }
        return true;
    }

    /**
     * @param Order|null $oOrder
     * @param string $unzerid
     * @param float $amount
     * @return UnzerApiException|bool
     */
    public function doUnzerCollect($oOrder, $unzerid, $amount)
    {
        if (!$oOrder) {
            return false;
        }

        try {
            $unzerPayment = $this->getUnzerSDK()->fetchPayment($unzerid);

            /** @psalm-suppress InvalidArgument */
            $charge = $unzerPayment->getAuthorization()->charge($amount);
            $this->transactionService->writeChargeToDB(
                $oOrder->getId(),
                $oOrder->oxorder__oxuserid->value,
                $charge
            );
            if (
                $charge->isSuccess() &&
                ($o = $charge->getPayment()) &&
                $o->getAmount()->getRemaining() == 0
            ) {
                $oOrder->markUnzerOrderAsPaid();
            }
        } catch (UnzerApiException $e) {
            return $e;
        }

        return true;
    }

    /**
     * @param Order|null $oOrder
     * @param string $unzerid
     * @return UnzerApiException|bool
     */
    public function doUnzerAuthorizationCancel($oOrder, $unzerid)
    {
        if (!$oOrder) {
            return false;
        }

        try {
            $unzerPayment = $this->getUnzerSDK()->fetchPayment($unzerid);

            /** @psalm-suppress InvalidArgument */
            $charge = $unzerPayment->getAuthorization()->cancel();
            $blSuccess = $this->transactionService->writeTransactionToDB(
                $oOrder->getId(),
                $oOrder->oxorder__oxuserid->value,
                $unzerPayment
            );
        } catch (UnzerApiException $e) {
            return $e;
        }

        return true;
    }

    /**
     * @param Order|null $oOrder
     * @param string $sPaymentId
     * @return UnzerApiException|bool
     */
    public function sendShipmentNotification($oOrder, $sPaymentId = null)
    {
        if (!$oOrder) {
            return false;
        }

        $sPaymentId = $sPaymentId ?? $this->transactionService->getPaymentIdByOrderId($oOrder->getId());

        $blSuccess = false;

        if ($sPaymentId) {
            $sInvoiceNr = $oOrder->getUnzerInvoiceNr();

            $unzerPayment = $this->getUnzerSDK()->fetchPayment($sPaymentId);

            if ($unzerPayment->getPaymentType() instanceof InstallmentSecured) {
                $this->setInstallmentDueDate($unzerPayment);
            }
            try {
                $shipment = $this->getUnzerSDK()->ship(
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
                    $blSuccess = $this->transactionService->writeTransactionToDB(
                        $oOrder->getId(),
                        $oOrder->oxorder__oxuserid->value,
                        $unzerPayment,
                        $unzerPayment->ship($sInvoiceNr)
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
