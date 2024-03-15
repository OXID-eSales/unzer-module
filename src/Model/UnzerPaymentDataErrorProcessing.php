<?php declare(strict_types=1);

namespace OxidEsales\EshopCommunity\modules\osc\unzer\src\Model;

class UnzerPaymentDataErrorProcessing
{
    public ?string $uniqueId = null;
    public ?string $shortId = null;

    /**
     * @param array $arrayPaymentData
     * @return self
     */
    public static function fromArray($arrayPaymentData)
    {
        $unzerPaymentData = new self();
        $unzerPaymentData->uniqueId = $arrayPaymentData['uniqueId'] ?? null;
        $unzerPaymentData->shortId = $arrayPaymentData['shortId'] ?? null;

        return $unzerPaymentData;
    }
}
