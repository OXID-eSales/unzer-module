<?php

namespace OxidSolutionCatalysts\Unzer\Controller\Admin;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\Order;
use OxidSolutionCatalysts\Unzer\Core\RefundMailService;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidSolutionCatalysts\Unzer\Model\Payment as UnzerPayment;
use OxidSolutionCatalysts\Unzer\Traits\Request;

class OrderList extends OrderList_parent
{
    use Request;

    /**
     * @param array $whereQuery SQL condition array
     * @param string $fullQuery SQL query string
     *
     * @return string
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     */
    protected function _prepareWhereQuery($whereQuery, $fullQuery)
    {
        // seperate oxordernr
        $orderNrSearch = '';
        if (isset($whereQuery['oxorder.oxordernr'])) {
            $orderNrSearch = $whereQuery['oxorder.oxordernr'];
            unset($whereQuery['oxorder.oxordernr']);
        }

        $database = DatabaseProvider::getDb();
        $query = parent::_prepareWhereQuery($whereQuery, $fullQuery);
        $config = $this->getConfig();
        $folders = $config->getConfigParam('aOrderfolder');
        $folder = $this->getUnzerStringRequestParameter('folder');
        // Searching for empty oxfolder fields
        if ($folder && $folder !== '-1') {
            $query .= " and ( oxorder.oxfolder = " . $database->quote($folder) . " )";
        } elseif (!$folder && is_array($folders)) {
            $folderNames = array_keys($folders);
            $query .= " and ( oxorder.oxfolder = " . $database->quote($folderNames[0]) . " )";
        }

        // glue oxordernr
        if ($orderNrSearch) {
            $oxOrderNr = $database->quoteIdentifier("oxorder.oxordernr");
            $oxUnzerOrderNr = $database->quoteIdentifier("oxorder.oxunzerordernr");
            $orderNrValue = $database->quote($orderNrSearch);
            $query .= " and ({$oxOrderNr} like {$orderNrValue} or {$oxUnzerOrderNr} like {$orderNrValue}) ";
        }

        return $query;
    }

    /**
     * Sends the cancellation confirmation mail after an order was cancelled in
     * the backend. An order cancellation moves no money in this module, so the
     * mail confirms the cancellation only; a refund is triggered separately in
     * the order view and confirmed separately.
     *
     * Orders of other payment methods and orders that cannot be loaded are passed
     * straight through to the parent implementation.
     *
     * @return void
     */
    public function cancelOrder()
    {
        $orderId = $this->getEditObjectId();
        if (!$orderId) {
            parent::cancelOrder();

            return;
        }

        $order = oxNew(Order::class);
        if (!$order->load($orderId) || !$this->isUnzerOrder($order)) {
            parent::cancelOrder();

            return;
        }

        parent::cancelOrder();

        $currency = $order->getFieldData('oxcurrency');
        $mailService = oxNew(RefundMailService::class);
        $mailService->sendCancelMail($order, null, is_scalar($currency) ? (string)$currency : '');
    }

    /**
     * Whether the order was paid with an unzer payment method, the same check the
     * order view uses: the payment id carries the module prefix and the payment
     * still exists.
     *
     * @param Order $order
     * @return bool
     */
    protected function isUnzerOrder(Order $order): bool
    {
        $paymentType = $order->getFieldData('oxpaymenttype');
        $paymentType = is_scalar($paymentType) ? (string)$paymentType : '';
        if (strpos($paymentType, 'oscunzer') === false) {
            return false;
        }

        /** @var UnzerPayment $payment */
        $payment = oxNew(Payment::class);

        return $payment->load($paymentType) && $payment->isUnzerPayment();
    }
}
