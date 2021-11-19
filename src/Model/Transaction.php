<?php

namespace OxidSolutionCatalysts\Unzer\Model;

use Exception;
use OxidEsales\Eshop\Core\Model\BaseModel;

class Transaction extends BaseModel
{
    /**
     * Class Name
     *
     * @var string
     */
    protected $_sClassName = Transaction::class;

    /**
     * Core table name
     *
     * @var string
     */
    protected $_sCoreTable = "oscunzertransaction";

    public function __construct()
    {
        parent::__construct();
        $this->init($this->_sCoreTable);
    }
}
