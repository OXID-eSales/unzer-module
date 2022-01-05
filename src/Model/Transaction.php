<?php

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

    public function getUnzerCreated(): ?string
    {
        return $this->getRawField('OXACTIONDATE');
    }

    public function getUnzerCustomerId(): ?string
    {
        return $this->getRawField('CUSTOMERID');
    }

    public function getUnzerAction(): ?string
    {
        return $this->getRawField('OXACTION');
    }

    public function getUnzerTypeId(): ?string
    {
        return $this->getRawField('TYPEID');
    }

    public function getUnzerMetaData()
    {
        $json = $this->getRawField('METADATA');
        if ($json) {
            return json_decode($json, true);
        }

        return [];
    }

    private function getRawField(string $sFieldName): ?string
    {
        $sLongFieldName = $this->_getFieldLongName($sFieldName);

        if (isset($this->{$sLongFieldName})) {
            $fieldData = $this->{$sLongFieldName};
            if ($fieldData instanceof Field) {
                $val = $fieldData->rawValue;

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
}
