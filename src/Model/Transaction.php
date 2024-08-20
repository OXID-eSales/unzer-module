<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Model\BaseModel;

class Transaction extends BaseModel
{
    /**
     * Class Name
     *
     * @var string
     */
    protected $_sClassName = Transaction::class; // phpcs:ignore

    /**
     * Core table name
     *
     * @var string
     */
    protected $_sCoreTable = "oscunzertransaction"; // phpcs:ignore

    public function __construct()
    {
        parent::__construct();
        $this->init($this->_sCoreTable);
    }

    /**
     * @return string|null
     */
    public function getUnzerCreated(): ?string
    {
        return $this->getRawField('OXACTIONDATE');
    }

    /**
     * @return string|null
     */
    public function getUnzerCustomerId(): ?string
    {
        return $this->getRawField('CUSTOMERID');
    }

    /**
     * @return string|null
     */
    public function getUnzerCustomerType(): ?string
    {
        return $this->getRawField('CUSTOMERTYPE');
    }
    /**
     * @return string|null
     */
    public function getUnzerState(): ?string
    {
        return $this->getRawField('OXACTION');
    }

    /**
     * @return string|null
     */
    public function getUnzerTypeId(): ?string
    {
        return $this->getRawField('TYPEID');
    }

    /**
     * @return string|null
     */
    public function getUnzerShortId(): ?string
    {
        return $this->getRawField('SHORTID');
    }

    /**
     * @return string|null
     */
    public function getUnzerCurrency(): ?string
    {
        return $this->getRawField('CURRENCY');
    }

    /**
     * @return string|null
     */
    public function getUnzerAmount(): ?string
    {
        return $this->getRawField('AMOUNT');
    }

    public function getUnzerRemaining(): ?string
    {
        if ($this->getUnzerState() === 'partly') {
            return $this->getRawField('REMAINING');
        }

        if ($this->getUnzerState() === 'canceled') {
            return $this->getUnzerAmount();
        }

        return $this->getUnzerAmount();
    }

    /**
     * @return string|null
     */
    public function getUnzerTraceId(): ?string
    {
        return $this->getRawField('TRACEID');
    }

    /**
     * @return array|null
     */
    public function getUnzerMetaData(): ?array
    {
        /** @var string $json */
        $json = $this->getRawField('METADATA');
        if ($json) {
            /** @var array $jsonDecoded */
            $jsonDecoded = json_decode($json, true);
            return $jsonDecoded;
        }

        return [];
    }

    /**
     * @param string $sFieldName
     * @return string|null
     */
    private function getRawField(string $sFieldName): ?string
    {
        $sLongFieldName = $this->_getFieldLongName($sFieldName);

        if (isset($this->{$sLongFieldName})) {
            $fieldData = $this->{$sLongFieldName};
            if ($fieldData instanceof Field) {
                $val = $fieldData->getRawValue();

                // Fix for MariaDB empty default-value issue with some oxid versions:
                // (causes quotes to be saved instead of empty string)
                if (is_string($val)) {
                    $val = trim($val, " \"'"); // remove surrounding quotes
                }

                return $val;
            }
        }

        return null;
    }

    public function setPaymentTypeId(?string $status): void
    {
        $status = $status ?: '';
        $this->_setFieldData('PAYMENTTYPEID', $status);
    }
}
