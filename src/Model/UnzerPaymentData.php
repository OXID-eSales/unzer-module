<?php

namespace OxidSolutionCatalysts\Unzer\Model;

class UnzerPaymentData
{
    /** @var string|null */
    public $id = null;
    /** @var bool|null */
    public $isSuccess = null;
    /** @var bool|null */
    public $isPending = null;
    /** @var bool|null */
    public $isResumed = null;
    /** @var bool|null */
    public $isError = null;
    /** @var string|null */
    public $url = null;
    /** @var string|null */
    public $timestamp = null;
    /** @var string|null */
    public $traceId = null;
    /** @var string|null */
    public $paymentId = null;
    /** @var UnzerPaymentDataError[] */
    public $errors = [];

    /**
     * @return string|null
     */
    public function getFirstErrorMessage()
    {
        return $this->errors[0]->customerMessage ?? null;
    }

    /**
     * @param array $arrayPaymentData
     */
    public function __construct($arrayPaymentData)
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
