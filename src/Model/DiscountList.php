<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Groups;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Model\ListModel;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\DiscountList as CoreDisCountList;
use OxidEsales\Eshop\Core\TableViewNameGenerator;

class DiscountList extends DiscountList_parent
{
    /**
     * Returns array of discounts that can be globally (transparently) applied
     *
     * @param Article $oArticle article object
     * @param User    $oUser    oxuser object (optional)
     *
     * @return array
     */
    public function getArticleDiscounts($oArticle, $oUser = null)
    {
        $aList = [];
        $this->forceReload();
        /** @var CoreDisCountList $oDiscList */
        $oDiscList = $this->getDiscountList($oUser);
        $aDiscList = $oDiscList->getArray();
        foreach ($aDiscList as $oDiscount) {
            if ($oDiscount->isForArticle($oArticle)) {
                $aList[$oDiscount->getId()] = $oDiscount;
            }
        }

        return $aList;
    }


    /**
     * Creates discount list filter SQL to load current state discount list
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     * @inheritdoc
     */
    protected function getFilterSelect($oUser)
    {
        $oBaseObject = $this->getBaseObject();
        $bDisableSqlActive = Registry::getSession()->getVariable('disableSqlActiveSnippet');
        $sTable = $oBaseObject->getViewName();
        $sSql = "select " . $oBaseObject->getSelectFields() . " from $sTable  where 1 ";
        $sSql .= false === $bDisableSqlActive ? $oBaseObject->getSqlActiveSnippet() . ' ' : '';

        // defining initial filter parameters
        $sUserId = null;
        $sGroupIds = null;
        $sCountryId = $this->getCountryId($oUser);
        $oDb = DatabaseProvider::getDb();

        // checking for current session user which gives additional restrictions for user itself,
        // users group and country
        if ($oUser) {
            // user ID
            $sUserId = $oUser->getId();

            // user group ids
            /** @var ListModel $userGroups */
            $userGroups = $oUser->getUserGroups();
            /** @var Groups $oGroup */
            foreach ($userGroups as $oGroup) {
                if ($sGroupIds) {
                    $sGroupIds .= ', ';
                }
                $sGroupIds .= $oDb->quote($oGroup->getId());
            }
        }

        $tabViewNameGenerator = oxNew(TableViewNameGenerator::class);
        $sUserTable = $tabViewNameGenerator->getViewName('oxuser');
        $sGroupTable = $tabViewNameGenerator->getViewName('oxgroups');
        $sCountryTable = $tabViewNameGenerator->getViewName('oxcountry');

        $sCountrySql = $sCountryId ?
            "EXISTS(select oxobject2discount.oxid from oxobject2discount where
            oxobject2discount.OXDISCOUNTID = $sTable.OXID and oxobject2discount.oxtype = 'oxcountry' and
            oxobject2discount.OXOBJECTID = " . $oDb->quote($sCountryId) . ")" :
            '0';
        $sUserSql = $sUserId ?
            "EXISTS(select oxobject2discount.oxid from oxobject2discount where
            oxobject2discount.OXDISCOUNTID = $sTable.OXID and oxobject2discount.oxtype = 'oxuser' and
            oxobject2discount.OXOBJECTID = " . $oDb->quote($sUserId) . ")" :
            '0';
        $sGroupSql = $sGroupIds ?
            "EXISTS(select oxobject2discount.oxid from oxobject2discount where
            oxobject2discount.OXDISCOUNTID = $sTable.OXID and oxobject2discount.oxtype = 'oxgroups' and
            oxobject2discount.OXOBJECTID in ($sGroupIds) )" :
            '0';

        $sSql .= "and (
                if(EXISTS(select 1 from oxobject2discount, $sCountryTable where
                $sCountryTable.oxid = oxobject2discount.oxobjectid and
                oxobject2discount.OXDISCOUNTID = $sTable.OXID and
                oxobject2discount.oxtype = 'oxcountry' LIMIT 1),
                        $sCountrySql,
                        1) &&
                if(EXISTS(select 1 from oxobject2discount, $sUserTable where
                $sUserTable.oxid = oxobject2discount.oxobjectid and
                oxobject2discount.OXDISCOUNTID = $sTable.OXID and
                oxobject2discount.oxtype = 'oxuser' LIMIT 1),
                        $sUserSql,
                        1) &&
                if(EXISTS(select 1 from oxobject2discount, $sGroupTable where 
                $sGroupTable.oxid = oxobject2discount.oxobjectid and 
                oxobject2discount.OXDISCOUNTID = $sTable.OXID and
                oxobject2discount.oxtype = 'oxgroups' LIMIT 1),
                        $sGroupSql,
                        1)
            )";

        $sSql .= " order by $sTable.oxsort ";

        return $sSql;
    }
}
