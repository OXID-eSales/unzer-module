<?php declare(strict_types=1);

namespace OxidEsales\EshopCommunity\modules\osc\unzer\src\Service;

use OxidSolutionCatalysts\Unzer\Model\UnzerPaymentData;

class UnzerPaymentDataParser
{
    public function parse($jsonPaymentData): UnzerPaymentData
    {
        $paymentData = $jsonPaymentData ? json_decode($jsonPaymentData, true) : [];
        return UnzerPaymentData::fromArray($paymentData);
    }
}