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
use OxidSolutionCatalysts\Unzer\Service\Transaction as TransactionService;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\AbstractUnzerResource;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Resources\PaymentTypes\InstallmentSecured;
use UnzerSDK\Resources\TransactionTypes\Authorization;

class Payment
{
    use ServiceContainer;

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

    /**
     * @param Session $session
     * @param PaymentExtensionLoader $paymentExtensionLoader
     * @param Translator $translator
     * @param Unzer $unzerService
     * @param UnzerSDKLoader $unzerSDKLoader
     */
    public function __construct(
        Session $session,
        PaymentExtensionLoader $paymentExtensionLoader,
        Translator $translator,
        Unzer $unzerService,
        UnzerSDKLoader $unzerSDKLoader
    ) {
        $this->session = $session;
        $this->paymentExtensionLoader = $paymentExtensionLoader;
        $this->translator = $translator;
        $this->unzerService = $unzerService;
        $this->unzerSDKLoader = $unzerSDKLoader;
    }

    /**
     * @throws Redirect
     * @throws RedirectWithMessage
     */
    public function executeUnzerPayment(PaymentModel $paymentModel): bool
    {
        try {
            $paymentExtension = $this->paymentExtensionLoader->getPaymentExtension($paymentModel);
            $paymentExtension->execute(
                $this->session->getUser(),
                $this->session->getBasket()
            );

            $paymentStatus = ($this->getUnzerPaymentStatus() != "ERROR");

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
                    isset($paymentExtension) ? $paymentExtension->redirectUrlNeedPending() : false
                ),
                $this->translator->translateCode($e->getErrorId(), $e->getClientMessage())
            );
        } catch (Exception $e) {
            $this->removeTemporaryOrder();

            throw new RedirectWithMessage(
                $this->unzerService->prepareOrderRedirectUrl(
                    isset($paymentExtension) ? $paymentExtension->redirectUrlNeedPending() : false
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
        $result = false;
        $sessionOrderId = $this->session->getVariable('sess_challenge');

        $orderModel = oxNew(Order::class);
        if ($orderModel->load($sessionOrderId)) {
            $result = $orderModel->delete();
        }

        return $result;
    }

    /**
     * @return string
     * @throws UnzerApiException
     */
    public function getUnzerPaymentStatus(): string
    {
        $result = "ERROR";

        /** @var \UnzerSDK\Resources\Payment $sessionUnzerPayment */
        $sessionUnzerPayment = $this->getSessionUnzerPayment();
        $transaction = $sessionUnzerPayment->getInitialTransaction();

        if ($sessionUnzerPayment->isCompleted()) {
            $result = "OK";
        } elseif ($sessionUnzerPayment->isPending() && $transaction) {
            if ($transaction->isSuccess()) {
                if ($transaction instanceof Authorization) {
                    $this->pdfLink = $transaction->getPDFLink();
                }
                if ($this->isPdfSession()) {
                    $this->pdfLink = null;
                    $result = "OK";
                }
            } elseif ($transaction->isPending()) {
                $result = "NOT_FINISHED";

                $this->redirectUrl = $transaction->getRedirectUrl();
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
        if ($paymentId = $this->session->getVariable('PaymentId')) {
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
        $transactionService = $this->getServiceFromContainer(TransactionService::class);
        try {
            $unzerPayment = $this->getServiceFromContainer(UnzerSDKLoader::class)
                ->getUnzerSDK()
                ->fetchChargeById($unzerid, $chargeid);

            $cancellation = $unzerPayment->cancel($amount, $reason);
            $transactionService->writeCancellationToDB(
                $oOrder->getId(),
                $oOrder->oxorder__oxuserid->value,
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

        $transactionService = $this->getServiceFromContainer(TransactionService::class);
        try {
            $unzerPayment = $this->getServiceFromContainer(UnzerSDKLoader::class)
                ->getUnzerSDK()
                ->fetchPayment($unzerid);

            $charge = $unzerPayment->getAuthorization()->charge($amount);
            $transactionService->writeChargeToDB(
                $oOrder->getId(),
                $oOrder->oxorder__oxuserid->value,
                $charge
            );
            if ($charge->isSuccess() && $charge->getPayment() && $charge->getPayment()->getAmount()->getRemaining() == 0) {
                $oOrder->markUnzerOrderAsPaid();
            }
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
        $transactionService = $this->getServiceFromContainer(TransactionService::class);

        if ($sPaymentId === null) {
            $sPaymentId = $transactionService->getPaymentIdByOrderId($oOrder->getId());
        }

        $blSuccess = false;

        if ($sPaymentId) {
            /** @var \UnzerSDK\Resources\Payment $unzerPayment */
            $unzerPayment = $this->getServiceFromContainer(UnzerSDKLoader::class)
                ->getUnzerSDK()
                ->fetchPayment($sPaymentId);

            if ($unzerPayment->getPaymentType() instanceof InstallmentSecured) {
                $this->setInstallmentDueDate($unzerPayment);
            }

            foreach ($unzerPayment->getShipments() as $unzShipment) {
                if ($unzShipment->isSuccess()) {
                    $blSuccess = true;
                }
            }

            if (!$blSuccess && $unzerPayment->getAmount()->getRemaining() === 0.0) {
                $sInvoiceNr = $oOrder->getUnzerInvoiceNr();
                try {
                    $blSuccess = $transactionService->writeTransactionToDB(
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
    public function setInstallmentDueDate($unzerPayment): void
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
