<?php

namespace OxidSolutionCatalysts\Unzer\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Model\Transaction;

/**
 * Order class wrapper for Unzer module
 */
class AdminOrderController extends AdminDetailsController
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
     * @throws DatabaseConnectionException
     */
    public function render(): string
    {
        parent::render();

        $this->_aViewData["sOxid"] = $this->getEditObjectId();
        if ($this->isUnzerOrder()) {
            $this->_aViewData['oOrder'] = $this->getEditObject();
            $this->_aViewData['oUnzerTransaction'] = Transaction::getTransactionByOxidOrderId($this->getEditObjectId());
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
    public function isUnzerOrder(): bool
    {
        $isUnzer = false;

        $order = $this->getEditObject();
        if ($order && strpos($order->getFieldData('oxpaymenttype'), "oscunzer") !== false) {
            $isUnzer = true;
        }

        return $isUnzer;
    }

    /**
     * Returns editable order object
     *
     * @return object
     */
    public function getEditObject(): ?object
    {
        $soxId = $this->getEditObjectId();
        if ($this->editObject === null && isset($soxId) && $soxId != '-1') {
            $this->editObject = oxNew(Order::class);
            $this->editObject->load($soxId);
        }

        return $this->editObject;
    }
}
