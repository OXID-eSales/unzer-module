<?php

namespace OxidSolutionCatalysts\Unzer\Controller\Admin;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;

class OrderList extends OrderList_parent
{
    /**
     * Adding folder check
    * bi *
     * @param array  $whereQuery SQL condition array
     * @param string $fullQuery  SQL query string
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     * @return string
     */
    protected function _prepareWhereQuery($whereQuery, $fullQuery)
    {
        // seperate oxordernr
        $orderNrSearch = '';
        if (isset($whereQuery['oxorder.oxordernr'])) {
            $orderNrSearch = $whereQuery['oxorder.oxordernr'];
            unset($whereQuery['oxorder.oxordernr']);
        }

        $database = DatabaseProvider::getDb();
        $query = parent::_prepareWhereQuery($whereQuery, $fullQuery);
        $config = $this->getConfig();
        $folders = $config->getConfigParam('aOrderfolder');
        $folder = Registry::getConfig()->getRequestParameter('folder');
        // Searching for empty oxfolder fields
        if ($folder && $folder !== '-1') {
            $query .= " and ( oxorder.oxfolder = " . $database->quote($folder) . " )";
        } elseif (!$folder && is_array($folders)) {
            $folderNames = array_keys($folders);
            $query .= " and ( oxorder.oxfolder = " . $database->quote($folderNames[0]) . " )";
        }

        // glue oxordernr
        if ($orderNrSearch) {
            $oxOrderNr = $database->quoteIdentifier("oxorder.oxordernr");
            $oxUnzerOrderNr = $database->quoteIdentifier("oxorder.oxunzerordernr");
            $orderNrValue = $database->quote($orderNrSearch);
            $query .= " and ({$oxOrderNr} like {$orderNrValue} or {$oxUnzerOrderNr} like {$orderNrValue}) ";
        }

        return $query;
    }
}
