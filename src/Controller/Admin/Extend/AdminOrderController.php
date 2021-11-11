<?php

namespace OxidSolutionCatalysts\Unzer\Controller\Admin\Extend;

use OxidEsales\Eshop\Core\Registry;

/**
 * Order class wrapper for Unzer module
 */
class AdminOrderController extends \OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController
{
    /**
     * Active order object
     *
     */
    protected $editObject = null;

    /**
     * Executes parent method parent::render()
     * name of template file "oscunzer_order.tpl".
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        $this->_aViewData["sOxid"] = $this->getEditObjectId();
        if ($this->isUnzerOrder()) {
            $this->_aViewData['oOrder'] = $this->getEditObject();
        } else {
            $this->_aViewData['sMessage'] = Registry::getLang()->translateString("OSCUNZER_NO_UNZER_ORDER");
        }

        return "oscunzer_order.tpl";
    }

    /**
     * Method checks is order was made with unzer payment
     *
     * @return bool
     */
    public function isUnzerOrder()
    {
        $isUnzer = false;

        $order = $this->getEditObject();
        if ($order && strpos($order->getFieldData('oxpaymenttype'), "oscunzer") !== false) {
            $isUnzer = true;
        }

        return $isUnzer;
    }

    /**
     * Returns active/editable object id
     *
     * @return string
     */
    public function getEditObjectId()
    {
        if (
            null === ($sId = $this->_sEditObjectId) &&
            null === ($sId = Registry::getConfig()->getRequestParameter("oxid"))
        ) {
            $sId = Registry::getSession()->getVariable("saved_oxid");
        }

        return $sId;
    }

    /**
     * Returns editable order object
     *
     * @return object
     */
    public function getEditObject()
    {
        $soxId = $this->getEditObjectId();
        if ($this->editObject === null && isset($soxId) && $soxId != '-1') {
            $this->editObject = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
            $this->editObject->load($soxId);
        }

        return $this->editObject;
    }
}
