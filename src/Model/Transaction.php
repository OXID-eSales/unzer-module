<?php

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\DatabaseProvider;

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
     * @throws DatabaseConnectionException
     *
     * @return false|self
     */
    public static function getTransactionByOxidOrderId(string $oxorderid)
    {
        if ($oxorderid) {
            $oxid = DatabaseProvider::getDb()->getOne(
                "SELECT OXID FROM oscunzertransaction WHERE OXORDERID=?",
                [$oxorderid]
            );

            if ($oxid) {
                /** @var Transaction $uzTransaction */
                $uzTransaction = oxNew(__CLASS__);
                if ($uzTransaction->load($oxid)) {
                    return $uzTransaction;
                }
            }
        }

        return false;
    }

    public function getUnzerCreated()
    {
        return $this->getRawField('OXACTIONDATE');
    }

    public function getUnzerCustomerId()
    {
        return $this->getRawField('CUSTOMERID');
    }

    public function getUnzerAction()
    {
        return $this->getRawField('OXACTION');
    }

    public function getUnzerTypeId()
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
