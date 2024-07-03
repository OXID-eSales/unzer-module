<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidEsales\Eshop\Core\Model\ListModel;
use OxidEsales\Eshop\Core\Registry;

class TransactionList extends ListModel
{
    /**
    * List Object class name
    *
    * @var string
    */
    protected $_sObjectsInListName = 'OxidSolutionCatalysts\Unzer\Model\Transaction'; // phpcs:ignore

    /**
     * @return void
     */
    public function getTransactionList(string $orderId, ?string $shortId = null): void
    {
        $oListObject = $this->getBaseObject();
        $sFieldList = $oListObject->getSelectFields();

        $shopId = Registry::getConfig()->getShopId();

        $params = [
            ':shopid' => $shopId,
            ':orderid' => $orderId
        ];

        if ($shortId) {
            $params[':shortid'] = $shortId;
        }

        $query = "select $sFieldList from " . $oListObject->getViewName() . "
            where oxshopid = :shopid
             and oxorderid = :orderid " .
            ($shortId ? " and shortid = :shortid " : "") . "
             order by {$oListObject->getViewName()}.OXTIMESTAMP asc";

        $this->selectString($query, $params);
    }
}
