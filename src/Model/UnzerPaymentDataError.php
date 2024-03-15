<?php declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidEsales\EshopCommunity\modules\osc\unzer\src\Model\UnzerPaymentDataErrorProcessing;

class UnzerPaymentDataError
{
    /** @var string|null */
    public $code = null;
    /** @var string|null */
    public $customerMessage = null;
    /** @var UnzerPaymentDataErrorStatus|null */
    public $status = null;

    /**
     * @param array $arrayPaymentData
     * @return self
     */
    public static function fromArray($arrayPaymentData)
    {
        $unzerPaymentData = new self();
        $unzerPaymentData->code = $arrayPaymentData['code'] ?? null;
        $unzerPaymentData->customerMessage = $arrayPaymentData['customerMessage'] ?? null;
        $unzerPaymentData->status = UnzerPaymentDataErrorStatus::fromArray(
            $arrayPaymentData['status'] ?? []
        );
        $unzerPaymentData->status = UnzerPaymentDataErrorProcessing::fromArray(
            $arrayPaymentData['processing'] ?? []
        );
        
        return $unzerPaymentData;
    }
}
