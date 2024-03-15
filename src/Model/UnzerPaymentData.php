<?php declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Model;

class UnzerPaymentData
{
    public ?string $id = null;
    public ?bool $isSuccess = null;
    public ?bool $isPending = null;
    public ?bool $isResumed = null;
    public ?bool $isError = null;
    public ?string $url = null;
    public ?string $timestamp = null;
    public ?string $traceId = null;
    public ?string $paymentId = null;
    /** @var UnzerPaymentDataError[] */
    public array $errors = [];

    /**
     * @return string|null
     */
    public function getFirstErrorMessage()
    {
        return $this->errors[0]->customerMessage ?? null;
    }

    public static function fromArray($arrayPaymentData): self
    {
        $unzerPaymentData = new self();
        $unzerPaymentData->id = $arrayPaymentData['id'] ?? null;
        $unzerPaymentData->isSuccess = $arrayPaymentData['isSuccess'] ?? null;
        $unzerPaymentData->isPending = $arrayPaymentData['isPending'] ?? null;
        $unzerPaymentData->isResumed = $arrayPaymentData['isResumed'] ?? null;
        $unzerPaymentData->isError = $arrayPaymentData['isError'] ?? null;
        $unzerPaymentData->url = $arrayPaymentData['url'] ?? null;
        $unzerPaymentData->timestamp = $arrayPaymentData['timestamp'] ?? null;
        $unzerPaymentData->traceId = $arrayPaymentData['traceId'] ?? null;
        $unzerPaymentData->paymentId = $arrayPaymentData['paymentId'] ?? null;
        $unzerPaymentData->errors = self::getErrors($arrayPaymentData['errors'] ?? []);

        return $unzerPaymentData;
    }

    private static function getErrors(array $arrayErrors): array
    {
        $errors = [];
        if (count($arrayErrors) > 0) {
            foreach ($arrayErrors as $arrayError) {
                $errors[] = UnzerPaymentDataError::fromArray($arrayError);
            }
        }

        return $errors;
    }
}
