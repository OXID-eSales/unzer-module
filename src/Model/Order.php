<?php

namespace OxidSolutionCatalysts\Unzer\Model;

use Exception;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\User;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use UnzerSDK\Exceptions\UnzerApiException;

class Order extends Order_parent
{
    /**
     * @param Basket $oBasket              Basket object
     * @param object $oUser                Current User object
     * @param bool                                       $blRecalculatingOrder Order recalculation
     * @return integer
     * @throws UnzerApiException
     */
    public function finalizeOrder(Basket $oBasket, object $oUser, bool $blRecalculatingOrder = false): int
    {
        $int = parent::finalizeOrder($oBasket, $oUser, $blRecalculatingOrder);

        if ($this->oxorder__oxtransstatus->value == "OK") {
            UnzerHelper::writeTransactionToDB($this->getId());
        }

        return $int;
    }
}
