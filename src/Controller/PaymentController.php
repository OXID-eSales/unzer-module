<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Controller;

use OxidEsales\Eshop\Application\Model\Order;
use OxidSolutionCatalysts\Unzer\Core\UnzerDefinitions;
use OxidSolutionCatalysts\Unzer\Model\TransactionList;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Registry;

class PaymentController extends PaymentController_parent
{
    use ServiceContainer;

    /**
     * @return bool
     */
    public function doSomething(): bool
    {
        return true;
    }

    /**
     * Executes parent method parent::render().
     */
    public function render()
    {
        $this->checkForUnzerPaymentErrors();
        return parent::render();
    }

    /**
     * Template variable getter. Returns paymentlist
     *
     * @return array<array-key, mixed>|object
     */
    public function getPaymentList()
    {
        $paymentList = (array)parent::getPaymentList();
        $moduleSettings = $this->getServiceFromContainer(ModuleSettings::class);

        if (!$moduleSettings->checkHealth()) {
            $paymentListRaw = $paymentList;
            $paymentList = [];

            foreach ($paymentListRaw as $key => $payment) {
                if ($payment->isUnzerPayment()) {
                    continue;
                }
                $paymentList[$key] = $payment;
            }
        } else {
            // check ApplePay Eligibility
            if (!$moduleSettings->isApplePayEligibility()) {
                unset($paymentList[UnzerDefinitions::APPLEPAY_UNZER_PAYMENT_ID]);
            }
        }

        return $paymentList;
    }

    protected function checkForUnzerPaymentErrors()
    {
        $payment = oxNew(Payment::class);
        if (
            $this->getPaymentError() &&
            ($actualPaymentId = $this->getCheckedPaymentId()) &&
            $payment->load($actualPaymentId) &&
            $payment->isUnzerPayment()
        ) {
            $session = Registry::getSession();
            $orderId = $session->getVariable('sess_challenge');
            $order = oxNew(Order::class);
            $order->delete($orderId);
            $session->deleteVariable('sess_challenge');
        }
    }
}
