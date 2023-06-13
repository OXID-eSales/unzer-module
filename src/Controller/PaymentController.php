<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Controller;

use OxidEsales\Eshop\Application\Model\Order;
use OxidSolutionCatalysts\Unzer\Service\UserRepository;
use OxidSolutionCatalysts\Unzer\Core\UnzerDefinitions as CoreUnzerDefinitions;
use OxidSolutionCatalysts\Unzer\Service\UnzerDefinitions;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Application\Controller\PaymentController as PaymentController_parent;
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
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getPaymentList()
    {
        $paymentList = (array)parent::getPaymentList();
        $moduleSettings = $this->getServiceFromContainer(ModuleSettings::class);
        $unzerDefinitions = $this->getServiceFromContainer(UnzerDefinitions::class)
            ->getDefinitionsArray();
        $actShopCurrency = Registry::getConfig()->getActShopCurrencyObject();
        $userRepository = $this->getServiceFromContainer(UserRepository::class);
        $userCountryIso = $userRepository->getUserCountryIso();

        $paymentListRaw = $paymentList;
        $paymentList = [];
        if (!$moduleSettings->checkHealth()) {
            /**
             * @var \OxidSolutionCatalysts\Unzer\Model\Payment $payment
             */
            foreach ($paymentListRaw as $key => $payment) {
                if (is_object($payment) && $payment->isUnzerPayment()) {
                    continue;
                }

                $paymentList[$key] = $payment;
            }
        } else {
            /**
             * @var \OxidSolutionCatalysts\Unzer\Model\Payment $payment
             */
            foreach ($paymentListRaw as $key => $payment) {
                if (
                    (
                        empty($unzerDefinitions[$key]['currencies']) ||
                        in_array($actShopCurrency->name, $unzerDefinitions[$key]['currencies'], true)
                    ) &&
                    (
                        empty($unzerDefinitions[$key]['countries']) ||
                        in_array($userCountryIso, $unzerDefinitions[$key]['countries'], true)
                    )
                ) {
                    $paymentList[$key] = $payment;
                }
            }
            // check ApplePay Eligibility
            if (!$moduleSettings->isApplePayEligibility()) {
                unset($paymentList[CoreUnzerDefinitions::APPLEPAY_UNZER_PAYMENT_ID]);
            }

            //check Invoice Eligibility
            if (!$moduleSettings->isInvoiceEligibility()) {
                unset($paymentList[CoreUnzerDefinitions::INVOICE_UNZER_PAYMENT_ID]);
            }
        }

        return $paymentList;
    }

    /**
     * @return void
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function checkForUnzerPaymentErrors(): void
    {
        /** @var \OxidSolutionCatalysts\Unzer\Model\Payment $payment */
        $payment = oxNew(Payment::class);
        $actualPaymentId = $this->getCheckedPaymentId();
        if (
            $this->getPaymentError() &&
            (is_string($actualPaymentId)) &&
            $payment->load($actualPaymentId) &&
            $payment->isUnzerPayment()
        ) {
            $session = Registry::getSession();
            /** @var string $orderId */
            $orderId = $session->getVariable('sess_challenge');
            $order = oxNew(Order::class);
            $order->delete($orderId);
            $session->deleteVariable('sess_challenge');
        }
    }
}
