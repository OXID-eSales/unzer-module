<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Model\Payment;
use OxidSolutionCatalysts\Unzer\Model\TransactionList;
use OxidSolutionCatalysts\Unzer\Service\Transaction as TransactionService;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\InstallmentSecured;
use UnzerSDK\Resources\PaymentTypes\InvoiceSecured;
use UnzerSDK\Resources\TransactionTypes\Cancellation;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\Resources\TransactionTypes\Shipment;

/**
 * Order class wrapper for Unzer module
 */
class AdminOrderController extends AdminDetailsController
{
    use ServiceContainer;

    /**
     * Active order object
     *
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

    protected function getUnzerViewData(string $sPaymentId): void
    {
        try {
            /** @var \UnzerSDK\Resources\Payment $unzerPayment */
            $unzerPayment = $this->getServiceFromContainer(UnzerSDKLoader::class)
                ->getUnzerSDK()
                ->fetchPayment($sPaymentId);
            $fCancelled = 0.0;
            $fCharged = 0.0;

            $paymentType = $unzerPayment->getPaymentType();

            $this->_aViewData["blShipment"] = (
                $paymentType instanceof InvoiceSecured ||
                $paymentType instanceof InstallmentSecured
            );

            $shipments = [];
            $this->_aViewData["uzrCurrency"] = $unzerPayment->getCurrency();

            /** @var Shipment $shipment */
            foreach ($unzerPayment->getShipments() as $shipment) {
                $aRv = [];
                $aRv['shipingDate'] = $shipment->getDate();
                $aRv['shipId'] = $shipment->getId();
                $aRv['invoiceid'] = $unzerPayment->getInvoiceId();
                $aRv['amount'] = $shipment->getAmount();

                $shipments[] = $aRv;
            }
            $this->_aViewData["aShipments"] = $shipments;

            if ($unzerPayment->getAuthorization()) {
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
                    if ($charge->isSuccess()) {
                        $aRv = [];
                        $aRv['chargedAmount'] = $charge->getAmount();
                        $aRv['cancelledAmount'] = $charge->getCancelledAmount();
                        $aRv['chargeId'] = $charge->getId();
                        $aRv['cancellationPossible'] = $charge->getAmount() > $charge->getCancelledAmount();
                        $fCharged += $charge->getAmount();
                        $aRv['chargeDate'] = $charge->getDate();

                        $charges [] = $aRv;
                    }
                }
            }

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

    public function sendShipmentNotification(): void
    {
        $unzerid = Registry::getRequest()->getRequestParameter('unzerid');
        $translator = $this->getServiceFromContainer(Translator::class);

        if ($unzerid) {
            $paymentService = $this->getServiceFromContainer(\OxidSolutionCatalysts\Unzer\Service\Payment::class);
            $oStatus = $paymentService->sendShipmentNotification($this->getEditObject(), $unzerid);

            if ($oStatus instanceof UnzerApiException) {
                $this->_aViewData['errShip'] = $translator->translateCode(
                    $oStatus->getErrorId(),
                    $oStatus->getMessage()
                );
            }
        }
    }

    public function doUnzerCollect(): void
    {
        $unzerid = Registry::getRequest()->getRequestParameter('unzerid');
        $amount = (float) Registry::getRequest()->getRequestParameter('amount');

        $translator = $this->getServiceFromContainer(Translator::class);

        $paymentService = $this->getServiceFromContainer(\OxidSolutionCatalysts\Unzer\Service\Payment::class);
        $oStatus = $paymentService->doUnzerCollect($this->getEditObject(), $unzerid, $amount);

        if ($oStatus instanceof UnzerApiException) {
            $this->_aViewData['errAuth'] = $translator->translateCode($oStatus->getErrorId(), $oStatus->getMessage());
        }
    }

    /**
     * @return void
     */
    public function doUnzerCancel()
    {
        $unzerid = Registry::getRequest()->getRequestParameter('unzerid');
        $chargeid = Registry::getRequest()->getRequestParameter('chargeid');
        $amount = (float) Registry::getRequest()->getRequestParameter('amount');
        $fCharged = (float) Registry::getRequest()->getRequestParameter('chargedamount');
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
        $oStatus = $paymentService->doUnzerCancel($this->getEditObject(), $unzerid, $chargeid, $amount, $reason);

        if ($oStatus instanceof UnzerApiException) {
            $this->_aViewData['errCancel'] = $translator->translateCode($oStatus->getErrorId(), $oStatus->getMessage());
        }
    }

    /**
     * @return void
     */
    public function doUnzerAuthorizationCancel()
    {
        $unzerid = Registry::getRequest()->getRequestParameter('unzerid');

        $translator = $this->getServiceFromContainer(Translator::class);

        $paymentService = $this->getServiceFromContainer(\OxidSolutionCatalysts\Unzer\Service\Payment::class);
        $oStatus = $paymentService->doUnzerAuthorizationCancel($this->getEditObject(), $unzerid);

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

        $order = $this->getEditObject();
        if ($order && strpos($order->getFieldData('oxpaymenttype'), "oscunzer") !== false) {
            $this->oPaymnet = oxNew(Payment::class);
            if ($this->oPaymnet->load($order->getFieldData('oxpaymenttype'))) {
                $isUnzer = true;
            }
        }

        return $isUnzer;
    }

    public function isCancelReasonRequired(): bool
    {
        if (!$this->oPaymnet) {
            return false;
        } else {
            return $this->oPaymnet->isUnzerSecuredPayment();
        }
    }
    /**
     * Returns editable order object
     *
     * @return Order|null
     */
    public function getEditObject(): ?object
    {
        $soxId = $this->getEditObjectId();
        if ($this->editObject === null && isset($soxId) && $soxId != '-1') {
            $this->editObject = oxNew(Order::class);
            $this->editObject->load($soxId);
        }

        return $this->editObject;
    }
}
