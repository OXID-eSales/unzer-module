<?php

namespace OxidSolutionCatalysts\Unzer\Controller\Admin;

/**
 * Order class wrapper for Unzer module
 */
class AdminOrderController extends \OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController
{
    /**
     * Active order object
     *
     */
    protected $_oEditObject = null;

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
            $this->_aViewData['sMessage'] = \OxidEsales\Eshop\Core\Registry::getLang()->translateString("OSCUNZER_NO_UNZER_ORDER");
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
        $active = false;

        $order = $this->getEditObject();
        if ($order && strpos($order->getFieldData('oxpaymenttype'), "oscunzer") !== false) {
            $active = true;
        }

        return $active;
    }

    /**
     * Returns active/editable object id
     *
     * @return string
     */
    public function getEditObjectId()
    {
        if (null === ($sId = $this->_sEditObjectId)) {
            if (null === ($sId = \OxidEsales\Eshop\Core\Registry::getConfig()->getRequestParameter("oxid"))) {
                $sId = \OxidEsales\Eshop\Core\Registry::getSession()->getVariable("saved_oxid");
            }
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
        if ($this->_oEditObject === null && isset($soxId) && $soxId != '-1') {
            $this->_oEditObject = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
            $this->_oEditObject->load($soxId);
        }

        return $this->_oEditObject;
    }
}
