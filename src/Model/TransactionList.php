<?php

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidEsales\Eshop\Core\Model\ListModel;
use OxidEsales\Eshop\Core\Registry;

class TransactionList extends ListModel
{
    /**
     * @var string
     */
    protected $_sObjectsInListName = TransactionList::class;

    /**
     * @var string
     */
    protected $_sCoreTable = "oscunzertransaction";

    /**
     * @return null
     */
    public function getTransactionList()
    {
        $oListObject = $this->getBaseObject();
        $sFieldList = $oListObject->getSelectFields();

        $oxConf = Registry::getConfig();
        $iShopId = $oxConf->getShopId();

        $params = [$iShopId];

        $sQ = "select $sFieldList from " . $oListObject->getViewName() . " where oxshopid = ? order by {$oListObject->getViewName()}.OXTIMESTAMP desc";

        $this->selectString($sQ, $params);
    }
}
