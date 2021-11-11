<?php

namespace OxidSolutionCatalysts\Unzer\Model\Extend;

class Payment extends Payment_parent
{
    /**
     * @return bool
     */
    public function isUnzerPayment(): bool
    {
        $isUnzer = false;

        if (strpos($this->oxpayments__oxid->value, "oscunzer") !== false) {
            $isUnzer = true;
        }

        return $isUnzer;
    }
}
