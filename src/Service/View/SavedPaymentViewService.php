<?php

namespace OxidSolutionCatalysts\Unzer\Service\View;

use OxidEsales\Eshop\Application\Model\User;
use OxidSolutionCatalysts\Unzer\Service\SavedPaymentService;
use OxidSolutionCatalysts\Unzer\Service\Transaction;

class SavedPaymentViewService
{
    private SavedPaymentService $savedPaymentService;
    private Transaction $transactionService;

    public function __construct(SavedPaymentService $savedPaymentService, Transaction $transactionService)
    {
        $this->savedPaymentService = $savedPaymentService;
        $this->transactionService = $transactionService;
    }

    public function setSavedPayPalPaymentsViewData(User $user, array &$viewData): void
    {
        $transaction = $this->savedPaymentService->getLastSavedPaymentTransaction(
            $user->getId(),
            SavedPaymentService::SAVED_PAYMENT_PAYPAL
        );

        if (!$transaction) {
            return;
        }

        $paymentTypes = $this->transactionService->getSavedPaymentsForUser($user, [$transaction], true);
        $oxidPayPalPaymentMethodId = $this->transactionService->getOxidPaymentMethodId(SavedPaymentService::SAVED_PAYMENT_PAYPAL);

        if (isset($paymentTypes[$oxidPayPalPaymentMethodId])) {
            $viewData['lastSavedPayPalPaymentType'] = reset($paymentTypes[$oxidPayPalPaymentMethodId]);
        }
    }
}