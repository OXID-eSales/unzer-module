<?php

namespace OxidSolutionCatalysts\Unzer\Model;

use Exception;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\User;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;

class Order extends Order_parent
{
    /**
     * @param Basket $oBasket
     * @param User $oUser
     * @param bool $blRecalculatingOrder
     * @return integer
     * @throws \UnzerSDK\Exceptions\UnzerApiException
     */
    public function finalizeOrder(Basket $oBasket, User $oUser, bool $blRecalculatingOrder = false): int
    {
        $int = parent::finalizeOrder($oBasket, $oUser, $blRecalculatingOrder);

        if ($this->oxorder__oxtransstatus->value == "OK") {
            UnzerHelper::writeTransactionToDB($this->getId());
        }

        return $int;
    }
}
