<?php

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
     */
    public function __construct($arrayPaymentData)
    {
        $this->successful = $arrayPaymentData['successful'] ?? null;
        $this->processing = $arrayPaymentData['processing'] ?? null;
        $this->pending = $arrayPaymentData['pending'] ?? null;
    }
}
