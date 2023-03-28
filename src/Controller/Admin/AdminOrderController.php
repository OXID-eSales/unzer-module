<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Core\UnzerDefinitions;
use OxidSolutionCatalysts\Unzer\Model\Payment;
use OxidSolutionCatalysts\Unzer\Model\Transaction;
use OxidSolutionCatalysts\Unzer\Model\TransactionList;
use OxidSolutionCatalysts\Unzer\Service\Transaction as TransactionService;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\InstallmentSecured;
use UnzerSDK\Resources\PaymentTypes\Invoice;
use UnzerSDK\Resources\PaymentTypes\Prepayment;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Resources\TransactionTypes\Cancellation;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\Resources\TransactionTypes\Shipment;
use UnzerSDK\Unzer;

/**
 * Order class wrapper for Unzer module
 *
 * TODO: Decrease count of dependencies to 13
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdminOrderController extends AdminDetailsController
{
    use ServiceContainer;

    /**
     * Active order object
     *
     * @var Order $editObject
     */
    protected $editObject = null;

    /** @var Payment $oPaymnet */
    protected $oPaymnet = null;

    /** @var string $sPaymentId */
    protected $sPaymentId;

    /**
     * Executes parent method parent::render()
     * name of template file "oscunzer_order.tpl".
     *
     * @return string
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function render(): string
    {
        parent::render();

        $this->_aViewData["sOxid"] = $this->getEditObjectId();

        $transactionList = oxNew(TransactionList::class);
        $transactionList->getTransactionList($this->getEditObjectId());
        if ($transactionList->count()) {
            $this->_aViewData['oUnzerTransactions'] = $transactionList;
        }

        if ($this->isUnzerOrder()) {
            /** @var Order $oOrder */
            $oOrder = $this->getEditObject();

            $this->_aViewData['paymentTitle'] = $this->oPaymnet->getFieldData('OXDESC');
            $this->_aViewData['oOrder'] = $oOrder;

            $transactionService = $this->getServiceFromContainer(TransactionService::class);
            $this->sPaymentId = $transactionService->getPaymentIdByOrderId($this->getEditObjectId());
            $this->_aViewData['sPaymentId'] = $this->sPaymentId;
            if ($this->sPaymentId) {
                $this->getUnzerViewData($this->sPaymentId);
            }
        } else {
            $translator = $this->getServiceFromContainer(Translator::class);
            $this->_aViewData['sMessage'] = $translator->translate("OSCUNZER_NO_UNZER_ORDER");
        }

        return "oscunzer_order.tpl";
    }

    public function getUnzerSDKbyPaymentId(string $sPaymentId): Unzer
    {
        return $this->getServiceFromContainer(UnzerSDKLoader::class)
                    ->getUnzerSDKbyPaymentType($sPaymentId);
    }

    public function getUnzerSDK(string $customerType = '', string $currency = ''): Unzer
    {
        return $this->getServiceFromContainer(UnzerSDKLoader::class)
                    ->getUnzerSDK($customerType, $currency);
    }

    /**
     * @param string $sPaymentId
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getUnzerViewData(string $sPaymentId): void
    {
        try {
            $transactionInfo = $this->getCustomerTypeAndCurrencyFromTransaction();
            // initialize proper SDK object
            $sdk = $this->getUnzerSDK($transactionInfo['customertype'], $transactionInfo['currency']);
            /** @var \UnzerSDK\Resources\Payment $unzerPayment */
            $unzerPayment = $sdk->fetchPayment($sPaymentId);
            $fCancelled = 0.0;
            $fCharged = 0.0;

            $paymentType = $unzerPayment->getPaymentType();

            $this->_aViewData['totalBasketPrice'] = sprintf(
                '%s %s',
                $this->getEditObject()->getFormattedTotalOrderSum(),
                $this->getEditObject()->getOrderCurrency()->name
            );
            $this->_aViewData["blShipment"] = (
                $paymentType instanceof InstallmentSecured
            );

            $isPrepaymentType = ($paymentType instanceof Prepayment);

            $shipments = [];
            $this->_aViewData["uzrCurrency"] = $unzerPayment->getCurrency();

            $blShipped = false;
            /** @var Shipment $shipment */
            foreach ($unzerPayment->getShipments() as $shipment) {
                $aRv = [];
                $aRv['shipingDate'] = $shipment->getDate();
                $aRv['shipId'] = $shipment->getId();
                $aRv['invoiceid'] = $unzerPayment->getInvoiceId();
                $aRv['amount'] = $shipment->getAmount();
                $aRv['success'] = $shipment->isSuccess();

                $blShipped = $shipment->isSuccess();
                $shipments[] = $aRv;
            }
            $this->_aViewData["aShipments"] = $shipments;
            $this->_aViewData["blSuccessShipped"] = $blShipped;

            if ($unzerPayment->getAuthorization()) {
                /** @var Authorization $unzAuthorization */
                $unzAuthorization = $unzerPayment->getAuthorization();

                $this->_aViewData["AuthAmountRemaining"] = $unzerPayment->getAmount()->getRemaining();
                $this->_aViewData["AuthFetchedAt"] = $unzAuthorization->getFetchedAt();
                $this->_aViewData["AuthShortId"] = $unzAuthorization->getShortId();
                $this->_aViewData["AuthId"] = $unzAuthorization->getId();
                $this->_aViewData["AuthAmount"] = $unzAuthorization->getAmount();
                $this->_aViewData['AuthCur'] = $unzerPayment->getCurrency();
            }

            $charges = [];
            if (!$unzerPayment->isCanceled()) {
                /** @var Charge $charge */
                foreach ($unzerPayment->getCharges() as $charge) {
                    if ($charge->isSuccess() || ($isPrepaymentType && $charge->isPending())) {
                        $aRv = [];
                        $aRv['chargedAmount'] = $charge->getAmount();
                        $aRv['cancelledAmount'] = $charge->getCancelledAmount();
                        $aRv['chargeId'] = $charge->getId();
                        $aRv['cancellationPossible'] = $charge->getAmount() > $charge->getCancelledAmount();
                        $fCharged += ($charge->isSuccess()) ? $charge->getAmount() : 0.;
                        $aRv['chargeDate'] = $charge->getDate();

                        $charges[] = $aRv;
                    }
                }
            }
            $this->_aViewData['totalAmountCharge'] = $fCharged;
            $this->_aViewData['remainingAmountCharge'] = $unzerPayment->getAmount()->getTotal() - $fCharged;

            $cancellations = [];
            /** @var Cancellation $cancellation */
            foreach ($unzerPayment->getCancellations() as $cancellation) {
                if ($cancellation->isSuccess()) {
                    $aRv = [];
                    $aRv['cancelledAmount'] = $cancellation->getAmount();
                    $aRv['cancelDate'] = $cancellation->getDate();
                    $aRv['cancellationId'] = $cancellation->getId();
                    $aRv['cancelReason'] = $cancellation->getReasonCode();
                    $fCancelled += $cancellation->getAmount();
                    $cancellations[] = $aRv;
                }
            }
            $this->_aViewData['totalAmountCancel'] = $fCancelled;
            $this->_aViewData['canCancelAmount'] = $fCharged - $fCancelled;

            $this->_aViewData['blCancellationAllowed'] = $fCancelled < $fCharged;
            $this->_aViewData['aCharges'] = $charges;
            $this->_aViewData['aCancellations'] = $cancellations;
            $this->_aViewData['blCancelReasonReq'] = $this->isCancelReasonRequired();
        } catch (\Exception $e) {
            Registry::getUtilsView()->addErrorToDisplay(
                $e->getMessage()
            );
        }
    }

    protected function getCustomerTypeAndCurrencyFromTransaction()
    {
        $transactionService = $this->getServiceFromContainer(TransactionService::class);
        return $transactionService->getCustomerTypeAndCurrencyByOrderId($this->getEditObjectId());
    }

    /**
     * @return void
     */
    public function sendShipmentNotification(): void
    {
        /** @var string $unzerid */
        $unzerid = Registry::getRequest()->getRequestParameter('unzerid');
        $translator = $this->getServiceFromContainer(Translator::class);

        if ($unzerid) {
            $paymentService = $this->getServiceFromContainer(\OxidSolutionCatalysts\Unzer\Service\Payment::class);
            /** @var \OxidSolutionCatalysts\Unzer\Model\Order $oOrder */
            $oOrder = $this->getEditObject();
            $oStatus = $paymentService->sendShipmentNotification($oOrder, $unzerid);

            if ($oStatus instanceof UnzerApiException) {
                $this->_aViewData['errShip'] = $translator->translateCode(
                    $oStatus->getErrorId(),
                    $oStatus->getMessage()
                );
            }
        }
    }

    /**
     * @return void
     */
    public function doUnzerCollect(): void
    {
        /** @var string $unzerid */
        $unzerid = Registry::getRequest()->getRequestParameter('unzerid');
        /** @var float $amount */
        $amount = Registry::getRequest()->getRequestParameter('amount');

        $translator = $this->getServiceFromContainer(Translator::class);

        $paymentService = $this->getServiceFromContainer(\OxidSolutionCatalysts\Unzer\Service\Payment::class);
        /** @var \OxidSolutionCatalysts\Unzer\Model\Order $oOrder */
        $oOrder = $this->getEditObject();
        $oStatus = $paymentService->doUnzerCollect($oOrder, $unzerid, $amount);

        if ($oStatus instanceof UnzerApiException) {
            $this->_aViewData['errAuth'] = $translator->translateCode($oStatus->getErrorId(), $oStatus->getMessage());
        }
    }

    /**
     * @return void
     */
    public function doUnzerCancel()
    {
        /** @var string $unzerid */
        $unzerid = Registry::getRequest()->getRequestParameter('unzerid');
        /** @var string $chargeid */
        $chargeid = Registry::getRequest()->getRequestParameter('chargeid');
        /** @var float $amount */
        $amount = Registry::getRequest()->getRequestParameter('amount');
        /** @var float $fCharged */
        $fCharged = Registry::getRequest()->getRequestParameter('chargedamount');
        /** @var string $reason */
        $reason = Registry::getRequest()->getRequestParameter('reason');

        $translator = $this->getServiceFromContainer(Translator::class);
        if ($reason === "NONE" && $this->isUnzerOrder() && $this->isCancelReasonRequired()) {
            $this->_aViewData['errCancel'] = $chargeid . ": "
                . $translator->translate('OSCUNZER_CANCEL_MISSINGREASON') . " " . $amount;
            return;
        }

        if ($reason === "NONE") {
            $reason = null;
        }

        if ($amount > $fCharged || $amount === 0.0) {
            $this->_aViewData['errCancel'] = $chargeid . ": "
                . $translator->translate('OSCUNZER_CANCEL_ERR_AMOUNT') . " " . $amount;
            return;
        }
        $paymentService = $this->getServiceFromContainer(\OxidSolutionCatalysts\Unzer\Service\Payment::class);
        /** @var Order $oOrder */
        $oOrder = $this->getEditObject();
        $oStatus = $paymentService->doUnzerCancel($oOrder, $unzerid, $chargeid, $amount, (string)$reason);

        if ($oStatus instanceof UnzerApiException) {
            $this->_aViewData['errCancel'] = $translator->translateCode($oStatus->getErrorId(), $oStatus->getMessage());
        }
    }

    /**
     * @return void
     */
    public function doUnzerAuthorizationCancel()
    {
        /** @var string $unzerid */
        $unzerid = Registry::getRequest()->getRequestParameter('unzerid');

        $translator = $this->getServiceFromContainer(Translator::class);

        $paymentService = $this->getServiceFromContainer(\OxidSolutionCatalysts\Unzer\Service\Payment::class);
        /** @var \OxidSolutionCatalysts\Unzer\Model\Order $oOrder */
        $oOrder = $this->getEditObject();
        $oStatus = $paymentService->doUnzerAuthorizationCancel($oOrder, $unzerid);

        if ($oStatus instanceof UnzerApiException) {
            $this->_aViewData['errAuth'] = $translator->translateCode($oStatus->getErrorId(), $oStatus->getMessage());
        }
    }

    /**
     * Method checks is order was made with unzer payment
     *
     * @return bool
     */
    public function isUnzerOrder(): bool
    {
        $isUnzer = false;

        /** @var Order $order */
        $order = $this->getEditObject();
        /** @var string $oxPaymentType */
        $oxPaymentType = $order->getFieldData('oxpaymenttype');
        if ($order instanceof Order && strpos($oxPaymentType, "oscunzer") !== false) {
            $this->oPaymnet = oxNew(Payment::class);
            if ($this->oPaymnet->load($oxPaymentType)) {
                $isUnzer = true;
            }
        }

        return $isUnzer;
    }

    /**
     * @return bool
     */
    public function isCancelReasonRequired(): bool
    {
        if (!($this->oPaymnet instanceof Payment)) {
            return false;
        }

        return $this->oPaymnet->isUnzerSecuredPayment();
    }
    /**
     * Returns editable order object
     *
     * @return Order|null
     */
    public function getEditObject(): ?object
    {
        $soxId = $this->getEditObjectId();
        if ($this->editObject === null && $soxId != '-1') {
            $this->editObject = oxNew(Order::class);
            $this->editObject->load($soxId);
        }

        return $this->editObject;
    }
}
