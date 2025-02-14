<?php

namespace OxidSolutionCatalysts\Unzer\Service\View;

use OxidEsales\Eshop\Application\Model\User;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentFetchPaymentTypeService;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentMapper;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentMethodValidator;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentPayPalMapper;
use OxidSolutionCatalysts\Unzer\Service\SavedPaymentLoadService;
use InvalidArgumentException;

class SavedPaymentViewService
{
    /** @var SavedPaymentLoadService $loadService */
    private $loadService;

    /** @var SavedPaymentMapper $mapper */
    private $mapper;

    /** @var SavedPaymentFetchPaymentTypeService $fetchService */
    private $fetchService;

    /** @var SavedPaymentMethodValidator $methodValidator */
    private $methodValidator;

    public function __construct(
        SavedPaymentLoadService $loadService,
        SavedPaymentMapper $mapper,
        SavedPaymentFetchPaymentTypeService $fetchService,
        SavedPaymentMethodValidator $methodValidator
    ) {
        $this->loadService = $loadService;
        $this->mapper = $mapper;
        $this->fetchService = $fetchService;
        $this->methodValidator = $methodValidator;
    }

    /**
     * @param string $savedPaymentMethod the parameter $savedPaymentMethod is one of the constants
     *      SavedPaymentService::SAVED_PAYMENT_PAYPAL, SavedPaymentService::SAVED_PAYMENT_CREDIT_CARD or
     *      SavedPaymentService::SAVED_PAYMENT_BOTH
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getSavedPayments(User $user, string $savedPaymentMethod): ?array
    {
        $this->validateSavePaymentMethod($savedPaymentMethod);

        $transactions = $this->loadService->getSavedPaymentTransactions(
            $user->getId(),
            $savedPaymentMethod
        );

        if (!$transactions) {
            return null;
        }

        $paymentTypes = $this->fetchService->fetchPaymentTypes($transactions);

        return $this->mapper->groupPaymentTypes($paymentTypes);
    }

    private function validateSavePaymentMethod(string $savedPaymentMethod): void
    {
        if (!$this->methodValidator->validate($savedPaymentMethod)) {
            throw new InvalidArgumentException(
                "Invalid savedPaymentMethod SavedPaymentViewService::getSavedPayPalPaymentsViewData"
                . ": $savedPaymentMethod"
            );
        }
    }
}
