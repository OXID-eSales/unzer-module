<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Controller;

use OxidEsales\Eshop\Application\Model\Order;
use OxidSolutionCatalysts\Unzer\Core\UnzerDefinitions as CoreUnzerDefinitions;
use OxidSolutionCatalysts\Unzer\Service\UserRepository;
use OxidSolutionCatalysts\Unzer\Service\UnzerDefinitions;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Registry;

class PaymentController extends PaymentController_parent
{
    use ServiceContainer;

    /**
     * Executes parent method parent::render().
     */
    public function render()
    {
        $template = parent::render();
        $this->checkForUnzerPaymentErrors();
        return $template;
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
        $unzerDefinitions = $this->getServiceFromContainer(UnzerDefinitions::class)
            ->getDefinitionsArray();
        $actShopCurrency = Registry::getConfig()->getActShopCurrencyObject();
        $userRepository = $this->getServiceFromContainer(UserRepository::class);
        $userCountryIso = $userRepository->getUserCountryIso();

        $paymentListRaw = $paymentList;
        $paymentList = [];

        /**
         * @var \OxidSolutionCatalysts\Unzer\Model\Payment $payment
         */
        foreach ($paymentListRaw as $key => $payment) {
            if (!is_object($payment)) {
                continue;
            }
            // any non-unzer payment ...
            if (!$payment->isUnzerPayment()) {
                $paymentList[$key] = $payment;
                continue;
            }

            if (
                (
                    $payment->isUnzerPaymentHealthy()
                ) &&
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
