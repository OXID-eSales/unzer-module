<?php

namespace OxidSolutionCatalysts\Unzer\Model;

use Exception;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\User;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use UnzerSDK\Exceptions\UnzerApiException;

class Order extends Order_parent
{
    public function finalizeOrder($oBasket, $oUser, $blRecalculatingOrder = false): int
    {
        $int = parent::finalizeOrder($oBasket, $oUser, $blRecalculatingOrder);

        if ($this->oxorder__oxtransstatus->value == "OK") {
            UnzerHelper::writeTransactionToDB($this->getId());
        }

        return $int;
    }
}
