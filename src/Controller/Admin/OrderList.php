<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Controller\Admin;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;

class OrderList extends OrderList_parent
{
    use ServiceContainer;

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
    protected function prepareWhereQuery($whereQuery, $fullQuery)
    {
        // seperate oxordernr
        $orderNrSearch = '';
        if (isset($whereQuery['oxorder.oxordernr'])) {
            $orderNrSearch = $whereQuery['oxorder.oxordernr'];
            unset($whereQuery['oxorder.oxordernr']);
        }

        $connectionProvider = $this->getServiceFromContainer(ConnectionProviderInterface::class)->get();

        $query = parent::prepareWhereQuery($whereQuery, $fullQuery);
        $folders = Registry::getConfig()->getConfigParam('aOrderfolder');
        $folder = Registry::getRequest()->getRequestEscapedParameter('folder');
        // Searching for empty oxfolder fields
        if ($folder && $folder !== '-1') {
            $query .= " and ( oxorder.oxfolder = " . $connectionProvider->quote($folder) . " )";
        } elseif (!$folder && is_array($folders)) {
            $folderNames = array_keys($folders);
            $query .= " and ( oxorder.oxfolder = " . $connectionProvider->quote($folderNames[0]) . " )";
        }

        // glue oxordernr
        if ($orderNrSearch) {
            $oxOrderNr = $connectionProvider->quoteIdentifier("oxorder.oxordernr");
            $oxUnzerOrderNr = $connectionProvider->quoteIdentifier("oxorder.oxunzerordernr");
            $orderNrValue = $connectionProvider->quote($orderNrSearch);
            $orderNrValue = is_string($orderNrValue) ? $orderNrValue : '';
            if ($orderNrValue) {
                $query .= " and ($oxOrderNr like $orderNrValue or $oxUnzerOrderNr like $orderNrValue) ";
            }
        }

        return $query;
    }

    /**
     * @param array $whereQuery
     * @param string $filterQuery
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     * @return string
     */
    protected function prepareOrderListQuery(array $whereQuery, string $filterQuery): string
    {
        if (count($whereQuery)) {
            $myUtilsString = Registry::getUtilsString();
            foreach ($whereQuery as $identifierName => $fieldValue) {
                //passing oxunzerordernr because it will be combined with oxordernr
                if ("oxorder.oxunzerordernr" === $identifierName) {
                    continue;
                }
                $fieldValue = trim($fieldValue);
                //check if this is search string (contains % sign at beginning and end of string)
                $isSearchValue = $this->isSearchValue($fieldValue);
                //removing % symbols
                $fieldValue = $this->processFilter($fieldValue);
                if ($fieldValue !== '') {
                    $connectionProvider = $this->getServiceFromContainer(ConnectionProviderInterface::class)->get();
                    $values = explode(' ', $fieldValue);
                    //for each search field using AND action
                    $queryBoolAction = ' and (';

                    //oxordernr is combined with oxunzerordernr
                    if ("oxorder.oxordernr" === $identifierName) {
                        $oxOrderNr = $connectionProvider->quoteIdentifier("oxorder.oxordernr");
                        $oxUnzerOrderNr = $connectionProvider->quoteIdentifier("oxorder.oxunzerordernr");
                        $orderNrQuery = [];
                        foreach ($values as $value) {
                            $value = $connectionProvider->quote($value);
                            $value = is_string($value) ? $value : '';
                            if ($value) {
                                $orderNrQuery[] = "($oxOrderNr like $value"
                                    . " or $oxUnzerOrderNr like $value)";
                            }
                        }
                        if ($orderNrQuery) {
                            $filterQuery .= "and (" . implode(" or ", $orderNrQuery) . ")";
                        }

                        continue;
                    }

                    foreach ($values as $value) {
                        // trying to search spec chars in search value
                        // if found, add cleaned search value to search sql
                        $uml = $myUtilsString->prepareStrForSearch($value);
                        if ($uml) {
                            $queryBoolAction .= '(';
                        }
                        $quotedIdentifierName = $connectionProvider->quoteIdentifier($identifierName);
                        $filterQuery .= " {$queryBoolAction} {$quotedIdentifierName} ";
                        //for search in same field for different values using AND
                        $queryBoolAction = ' and ';
                        $filterQuery .= $this->buildFilter($value, $isSearchValue);
                        if ($uml) {
                            $filterQuery .= " or $quotedIdentifierName ";

                            $filterQuery .= $this->buildFilter($uml, $isSearchValue);
                            $filterQuery .= ')'; // end of OR section
                        }
                    }
                        // end for AND action
                        $filterQuery .= ' ) ';
                }
            }
        }

        return $filterQuery;
    }

    /**
     * Returns list filter array
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     * @return array
     */
    public function getListFilter(): array
    {
        if ($this->_aListFilter === null) {
            $this->_aListFilter = [];
            $request = Registry::getRequest();
            $filter = $request->getRequestParameter("where");
            $request->checkParamSpecialChars($filter);

            if (is_array($filter) && !empty($filter['oxorder']['oxordernr'])) {
                $filter['oxorder']['oxunzerordernr'] = $filter['oxorder']['oxordernr'];
                $this->_aListFilter = $filter;
            }
        }

        return $this->_aListFilter;
    }
}
