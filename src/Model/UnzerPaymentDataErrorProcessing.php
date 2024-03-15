<?php

namespace OxidEsales\EshopCommunity\modules\osc\unzer\src\Model;

class UnzerPaymentDataErrorProcessing
{
    /** @var string|null */
    public $uniqueId = null;
    /** @var string|null */
    public $shortId = null;

    /**
     * @param array $arrayPaymentData
     * @return self
     */
    public function __construct($arrayPaymentData)
    {
        $this->uniqueId = $arrayPaymentData['uniqueId'] ?? null;
        $this->shortId = $arrayPaymentData['shortId'] ?? null;
    }
}
