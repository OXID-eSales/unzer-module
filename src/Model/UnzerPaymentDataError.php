<?php

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidSolutionCatalysts\Unzer\Model\UnzerPaymentDataErrorProcessing;

class UnzerPaymentDataError
{
    /** @var string|null */
    public $code = null;
    /** @var string|null */
    public $customerMessage = null;
    /** @var UnzerPaymentDataErrorStatus|null */
    public $status = null;
    /** @var UnzerPaymentDataErrorProcessing|null */
    public $processing = null;

    /**
     * @param array $arrayPaymentData
     */
    public function __construct($arrayPaymentData)
    {
        $this->code = $arrayPaymentData['code'] ?? null;
        $this->customerMessage = $arrayPaymentData['customerMessage'] ?? null;
        $this->status = new UnzerPaymentDataErrorStatus(
            $arrayPaymentData['status'] ?? []
        );
        $this->processing = new UnzerPaymentDataErrorProcessing(
            $arrayPaymentData['processing'] ?? []
        );
    }
}
