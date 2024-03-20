<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Model;

class UnzerPaymentData
{
    /** @var string|null */
    public string|null $id = null;
    public bool|null $isSuccess = null;
    public bool|null $isPending = null;
    public bool|null $isResumed = null;
    public bool|null $isError = null;
    public string|null $url = null;
    public string|null $timestamp = null;
    public string|null $traceId = null;
    public string|null $paymentId = null;
    /** @var UnzerPaymentDataError[] */
    public array $errors = [];

    /**
     * @return string|null
     */
    public function getFirstErrorMessage(): ?string
    {
        return $this->errors[0]->customerMessage ?? null;
    }

    public function __construct(array $arrayPaymentData)
    {
        $this->id = $arrayPaymentData['id'] ?? null;
        $this->isSuccess = $arrayPaymentData['isSuccess'] ?? null;
        $this->isPending = $arrayPaymentData['isPending'] ?? null;
        $this->isResumed = $arrayPaymentData['isResumed'] ?? null;
        $this->isError = $arrayPaymentData['isError'] ?? null;
        $this->url = $arrayPaymentData['url'] ?? null;
        $this->timestamp = $arrayPaymentData['timestamp'] ?? null;
        $this->traceId = $arrayPaymentData['traceId'] ?? null;
        $this->paymentId = $arrayPaymentData['paymentId'] ?? null;
        $this->errors = $this->getErrors($arrayPaymentData['errors'] ?? []);
    }

    private function getErrors(array $arrayErrors): array
    {
        $errors = [];
        if (count($arrayErrors) > 0) {
            foreach ($arrayErrors as $arrayError) {
                $errors[] = new UnzerPaymentDataError($arrayError);
            }
        }

        return $errors;
    }
}
