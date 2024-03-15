<?php declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Model;

class UnzerPaymentDataErrorStatus
{
    /** @var bool|null */
    public $successful = null;
    /** @var bool|null */
    public $processing = null;
    /** @var bool|null */
    public $pending = null;

    /**
     * @param array $arrayPaymentData
     * @return self
     */
    public static function fromArray($arrayPaymentData)
    {
        $unzerPaymentData = new self();
        $unzerPaymentData->successful = $arrayPaymentData['successful'] ?? null;
        $unzerPaymentData->processing = $arrayPaymentData['processing'] ?? null;
        $unzerPaymentData->pending = $arrayPaymentData['pending'] ?? null;

        return $unzerPaymentData;
    }
}
