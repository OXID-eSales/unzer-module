<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\Registry;
use UnzerSDK\Resources\Payment;

class TmpFetchPayment extends BaseModel
{
    public const CORE_TABLE = 'oscunzerfetchpayment';
    /**
     * Class constructor, initiates parent constructor.
     * @codeCoverageIgnore
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->init(self::CORE_TABLE);
    }

    public function save()
    {
        return parent::save();
    }

    /**
     * @param string|null $unzerPaymentId
     * @param \UnzerSDK\Resources\Payment|null $response
     * @return void
     * @throws \Exception
     */
    public function saveFetchPayment(?string $unzerPaymentId, ?Payment $response): void
    {
        $oConfig = Registry::getConfig();

        $this->assign([
            'oxid'               => $unzerPaymentId,
            'oxshopid'           => $oConfig->getShopId(),
            'fetchpayment'       => $this->serializeEncode($response),
            'timestamp'          => date('Y-m-d H:i:s')
        ]);

        $this->save();
    }


    /**
     * @param string $unzerPaymentId
     * @return \UnzerSDK\Resources\Payment
     */
    public function loadFetchPayment(string $unzerPaymentId): ?Payment
    {
        $this->load($unzerPaymentId);

        /**  @phpstan-ignore-next-line */
        $field = $this->getRawFieldData('FETCHPAYMENT');
        if (is_string($field)) {
            $decoded = $this->decodeUnserialize($field);
            return ($decoded instanceof Payment) ? $decoded : null;
        }

        return null;
    }

    /**
     * @param \UnzerSDK\Resources\Payment|null $data
     * @return string
     */
    private function serializeEncode(?Payment $data): string
    {
        $serializedOrder = serialize($data);
        return base64_encode($serializedOrder);
    }

    /**
     * @return mixed
     */
    private function decodeUnserialize(string $data)
    {
        $decoded = base64_decode($data);
        return unserialize($decoded);
    }
}
