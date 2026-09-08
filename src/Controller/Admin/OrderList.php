<?php

namespace OxidSolutionCatalysts\Unzer\Controller\Admin;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\Order;
use OxidSolutionCatalysts\Unzer\Core\RefundMailService;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidSolutionCatalysts\Unzer\Model\Payment as UnzerPayment;
use OxidSolutionCatalysts\Unzer\Service\DebugHandler;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use OxidSolutionCatalysts\Unzer\Service\Payment as UnzerPaymentService;
use OxidSolutionCatalysts\Unzer\Service\Transaction as TransactionService;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use OxidSolutionCatalysts\Unzer\Traits\Request;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use Throwable;
use UnzerSDK\Exceptions\UnzerApiException;

class OrderList extends OrderList_parent
{
    use Request;
    use ServiceContainer;

    /**
     * Reason code an automated refund of a cancellation is booked with at unzer. Secured
     * payment methods require one, and of the three the order view offers (CANCEL, RETURN,
     * CREDIT) a cancellation is exactly the first.
     */
    private const CANCEL_REASON = 'CANCEL';

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
     * Cancels an order in the backend and, when the merchant asked for it, refunds what the
     * unzer payment still holds. The confirmation mail goes out afterwards and states the
     * refunded amount if one was refunded, so the customer gets one mail for the whole event.
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

        $refundedAmount = $this->refundOnCancel($order);

        $currency = $order->getFieldData('oxcurrency');
        $mailService = oxNew(RefundMailService::class);
        $mailService->sendCancelMail($order, $refundedAmount, is_scalar($currency) ? (string)$currency : '');
    }

    /**
     * Refunds what the unzer payment still holds, if the merchant switched that on
     * (module setting UnzerAutomatedRefundOnCancel, off by default).
     *
     * Only the remaining amount is refunded - charged minus already cancelled: a cancellation
     * cancels the whole order, and an amount that went back to the customer before must not be
     * sent a second time. Nothing left to refund means nothing happens, which is also the case
     * for a payment that was only authorized and never charged - reverting an authorization is
     * a different operation and stays with the order view.
     *
     * The refund goes through Service\Payment::doUnzerCancel() without a charge id, which
     * cancels the charged payment as a whole and books the cancellation into the module's
     * transaction table, exactly like the refund in the order view does.
     *
     * A refund that does not work out must never undo the cancellation: the order is cancelled
     * at this point, so the problem is logged, reported to the merchant and left at that - the
     * order view is still there to refund by hand.
     *
     * @param Order $order
     * @return float|null amount unzer confirmed as refunded, null when nothing was refunded
     */
    protected function refundOnCancel(Order $order): ?float
    {
        /** @var ModuleSettings $moduleSettings */
        $moduleSettings = $this->getServiceFromContainer(ModuleSettings::class);
        if (!$moduleSettings->automatedRefundOnCancel()) {
            return null;
        }

        $orderNr = $order->getFieldData('oxordernr');
        $orderNr = is_scalar($orderNr) ? (string)$orderNr : '';

        try {
            /** @var TransactionService $transactionService */
            $transactionService = $this->getServiceFromContainer(TransactionService::class);
            $unzerPaymentId = $transactionService->getPaymentIdByOrderId($order->getId(), true);
            if ($unzerPaymentId === '') {
                return null;
            }

            $refundableAmount = $this->refundableAmount($unzerPaymentId);
            if ($refundableAmount <= 0.0) {
                return null;
            }

            /** @var UnzerPaymentService $paymentService */
            $paymentService = $this->getServiceFromContainer(UnzerPaymentService::class);
            $result = $paymentService->doUnzerCancel(
                $order,
                $unzerPaymentId,
                '',
                $refundableAmount,
                self::CANCEL_REASON
            );

            if ($result !== true) {
                $this->log(sprintf(
                    'Unzer automated refund on cancellation of order %s was refused: %s',
                    $orderNr,
                    // doUnzerCancel() answers with the exception instead of throwing it
                    $result instanceof Throwable ? $result->getMessage() : 'unknown reason'
                ));
                Registry::getUtilsView()->addErrorToDisplay('OSCUNZER_CANCEL_REFUND_FAILED');

                return null;
            }

            return $refundableAmount;
        } catch (Throwable $throwable) {
            $this->log(sprintf(
                'Unzer automated refund on cancellation of order %s failed: %s',
                $orderNr,
                $throwable->getMessage()
            ));
            Registry::getUtilsView()->addErrorToDisplay('OSCUNZER_CANCEL_REFUND_FAILED');

            return null;
        }
    }

    /**
     * What the unzer payment still holds: charged minus already cancelled, rounded to the two
     * decimals an amount is sent with.
     *
     * @param string $unzerPaymentId unzer payment id (i.e. s-pay-XXXX)
     * @return float
     * @throws UnzerApiException
     */
    protected function refundableAmount(string $unzerPaymentId): float
    {
        $payment = $this->getServiceFromContainer(UnzerSDKLoader::class)
            ->getUnzerSDKbyPaymentType($unzerPaymentId)
            ->fetchPayment($unzerPaymentId);
        $amount = $payment->getAmount();

        return round($amount->getCharged() - $amount->getCanceled(), 2);
    }

    /**
     * @param string $message
     * @return void
     */
    protected function log(string $message): void
    {
        try {
            $this->getServiceFromContainer(DebugHandler::class)->log($message);
        } catch (Throwable $throwable) {
            // logging must not break the cancellation either
        }
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
