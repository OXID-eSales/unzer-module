<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Controller\Admin;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidSolutionCatalysts\Unzer\Core\RefundMailService;
use OxidSolutionCatalysts\Unzer\Model\Order as UnzerOrderModel;
use OxidSolutionCatalysts\Unzer\Model\Payment as UnzerPayment;
use OxidSolutionCatalysts\Unzer\Service\DebugHandler;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use OxidSolutionCatalysts\Unzer\Service\Payment as UnzerPaymentService;
use OxidSolutionCatalysts\Unzer\Service\Transaction as TransactionService;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use Throwable;
use UnzerSDK\Exceptions\UnzerApiException;

class OrderList extends OrderList_parent
{
    use ServiceContainer;

    /**
     * Reason code an automated refund of a cancellation is booked with at unzer. Secured
     * payment methods require one, and of the three the order view offers (CANCEL, RETURN,
     * CREDIT) a cancellation is exactly the first.
     */
    private const CANCEL_REASON = 'CANCEL';

    /**
     * Adding folder check
    * bi *
     * @param array  $whereQuery SQL condition array
     * @param string $fullQuery  SQL query string
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     * @return string
     */
    protected function prepareWhereQuery($whereQuery, $fullQuery)
    {
        // seperate oxordernr
        $orderNrSearch = '';
        if (isset($whereQuery['oxorder.oxordernr'])) {
            $orderNrSearch = $whereQuery['oxorder.oxordernr'];
            unset($whereQuery['oxorder.oxordernr']);
        }

        $connectionProvider = $this->getServiceFromContainer(ConnectionProviderInterface::class)->get();

        $query = parent::prepareWhereQuery($whereQuery, $fullQuery);
        $folders = Registry::getConfig()->getConfigParam('aOrderfolder');
        $folder = Registry::getRequest()->getRequestEscapedParameter('folder');
        // Searching for empty oxfolder fields
        if ($folder && $folder !== '-1') {
            $query .= " and ( oxorder.oxfolder = " . $connectionProvider->quote($folder) . " )";
        } elseif (!$folder && is_array($folders)) {
            $folderNames = array_keys($folders);
            $query .= " and ( oxorder.oxfolder = " . $connectionProvider->quote($folderNames[0]) . " )";
        }

        // glue oxordernr
        if ($orderNrSearch) {
            $oxOrderNr = $connectionProvider->quoteIdentifier("oxorder.oxordernr");
            $oxUnzerOrderNr = $connectionProvider->quoteIdentifier("oxorder.oxunzerordernr");
            $orderNrValue = $connectionProvider->quote($orderNrSearch);
            $orderNrValue = is_string($orderNrValue) ? $orderNrValue : '';
            if ($orderNrValue) {
                $query .= " and ($oxOrderNr like $orderNrValue or $oxUnzerOrderNr like $orderNrValue) ";
            }
        }

        return $query;
    }

    /**
     * @param array $whereQuery
     * @param string $filterQuery
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     * @return string
     */
    protected function prepareOrderListQuery(array $whereQuery, string $filterQuery): string
    {
        if (count($whereQuery)) {
            $myUtilsString = Registry::getUtilsString();
            foreach ($whereQuery as $identifierName => $fieldValue) {
                //passing oxunzerordernr because it will be combined with oxordernr
                if ("oxorder.oxunzerordernr" === $identifierName) {
                    continue;
                }
                $fieldValue = trim($fieldValue);
                //check if this is search string (contains % sign at beginning and end of string)
                $isSearchValue = $this->isSearchValue($fieldValue);
                //removing % symbols
                $fieldValue = $this->processFilter($fieldValue);
                if ($fieldValue !== '') {
                    $connectionProvider = $this->getServiceFromContainer(ConnectionProviderInterface::class)->get();
                    $values = explode(' ', $fieldValue);
                    //for each search field using AND action
                    $queryBoolAction = ' and (';

                    //oxordernr is combined with oxunzerordernr
                    if ("oxorder.oxordernr" === $identifierName) {
                        $oxOrderNr = $connectionProvider->quoteIdentifier("oxorder.oxordernr");
                        $oxUnzerOrderNr = $connectionProvider->quoteIdentifier("oxorder.oxunzerordernr");
                        $orderNrQuery = [];
                        foreach ($values as $value) {
                            $value = $connectionProvider->quote($value);
                            $value = is_string($value) ? $value : '';
                            if ($value) {
                                $orderNrQuery[] = "($oxOrderNr like $value"
                                    . " or $oxUnzerOrderNr like $value)";
                            }
                        }
                        if ($orderNrQuery) {
                            $filterQuery .= "and (" . implode(" or ", $orderNrQuery) . ")";
                        }

                        continue;
                    }

                    foreach ($values as $value) {
                        // trying to search spec chars in search value
                        // if found, add cleaned search value to search sql
                        $uml = $myUtilsString->prepareStrForSearch($value);
                        if ($uml) {
                            $queryBoolAction .= '(';
                        }
                        $quotedIdentifierName = $connectionProvider->quoteIdentifier($identifierName);
                        $filterQuery .= " {$queryBoolAction} {$quotedIdentifierName} ";
                        //for search in same field for different values using AND
                        $queryBoolAction = ' and ';
                        $filterQuery .= $this->buildFilter($value, $isSearchValue);
                        if ($uml) {
                            $filterQuery .= " or $quotedIdentifierName ";

                            $filterQuery .= $this->buildFilter($uml, $isSearchValue);
                            $filterQuery .= ')'; // end of OR section
                        }
                    }
                        // end for AND action
                        $filterQuery .= ' ) ';
                }
            }
        }

        return $filterQuery;
    }

    /**
     * Returns list filter array
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     * @return array
     */
    public function getListFilter(): array
    {
        if ($this->_aListFilter === null) {
            $this->_aListFilter = [];
            $request = Registry::getRequest();
            $filter = $request->getRequestParameter("where");
            $request->checkParamSpecialChars($filter);

            if (is_array($filter) && !empty($filter['oxorder']['oxordernr'])) {
                $filter['oxorder']['oxunzerordernr'] = $filter['oxorder']['oxordernr'];
                $this->_aListFilter = $filter;
            }
        }

        return $this->_aListFilter;
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
            /** @var UnzerOrderModel $order */
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
