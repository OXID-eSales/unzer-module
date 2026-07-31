<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Core;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;

/**
 * Confirmation mails for refunds and order cancellations triggered in the
 * backend. Rendering and recipient handling only; whether a mail is sent at all
 * is decided by RefundMailService, which is the single caller.
 *
 * @mixin \OxidEsales\Eshop\Core\Email
 */
class Email extends Email_parent
{
    /**
     * Refund confirmation - HTML
     *
     * @var string
     */
    protected $unzerRefundTplHtml = "modules/osc/unzer/email/html/refund.tpl";

    /**
     * Refund confirmation - Plain
     *
     * @var string
     */
    protected $unzerRefundTplPlain = "modules/osc/unzer/email/plain/refund.tpl";

    /**
     * Cancellation confirmation - HTML
     *
     * @var string
     */
    protected $unzerCancelTplHtml = "modules/osc/unzer/email/html/cancel.tpl";

    /**
     * Cancellation confirmation - Plain
     *
     * @var string
     */
    protected $unzerCancelTplPlain = "modules/osc/unzer/email/plain/cancel.tpl";
    /**
     * @param Order $order
     * @param float $refundedAmount amount Unzer confirmed as refunded
     * @param string $currency currency code of the refunded amount
     * @return bool
     */
    public function sendUnzerRefundMailToCustomer(
        Order $order,
        float $refundedAmount,
        string $currency
    ): bool {
        return $this->sendUnzerRefundMail($order, $refundedAmount, $currency, false);
    }

    /**
     * @param Order $order
     * @param float $refundedAmount amount Unzer confirmed as refunded
     * @param string $currency currency code of the refunded amount
     * @return bool
     */
    public function sendUnzerRefundMailToOwner(
        Order $order,
        float $refundedAmount,
        string $currency
    ): bool {
        return $this->sendUnzerRefundMail($order, $refundedAmount, $currency, true);
    }

    /**
     * @param Order $order
     * @param float|null $refundedAmount amount refunded along with the
     *                                   cancellation, null if no refund was made
     * @param string $currency currency code of the refunded amount
     * @return bool
     */
    public function sendUnzerCancelMailToCustomer(
        Order $order,
        ?float $refundedAmount,
        string $currency
    ): bool {
        return $this->sendUnzerCancelMail($order, $refundedAmount, $currency, false);
    }

    /**
     * @param Order $order
     * @param float|null $refundedAmount amount refunded along with the
     *                                   cancellation, null if no refund was made
     * @param string $currency currency code of the refunded amount
     * @return bool
     */
    public function sendUnzerCancelMailToOwner(
        Order $order,
        ?float $refundedAmount,
        string $currency
    ): bool {
        return $this->sendUnzerCancelMail($order, $refundedAmount, $currency, true);
    }

    /**
     * @param Order $order
     * @param float $refundedAmount
     * @param string $currency
     * @param bool $toOwner send to the shop owner instead of the customer
     * @return bool
     */
    protected function sendUnzerRefundMail(
        Order $order,
        float $refundedAmount,
        string $currency,
        bool $toOwner
    ): bool {
        return $this->sendUnzerOrderMail(
            $order,
            $toOwner,
            $this->unzerRefundTplHtml,
            $this->unzerRefundTplPlain,
            $toOwner ? 'OSCUNZER_REFUND_MAIL_SUBJECT_OWNER' : 'OSCUNZER_REFUND_MAIL_SUBJECT',
            [
                'unzerRefundedAmount' => $refundedAmount,
                'unzerCurrencyCode' => $currency,
            ]
        );
    }

    /**
     * @param Order $order
     * @param float|null $refundedAmount
     * @param string $currency
     * @param bool $toOwner send to the shop owner instead of the customer
     * @return bool
     */
    protected function sendUnzerCancelMail(
        Order $order,
        ?float $refundedAmount,
        string $currency,
        bool $toOwner
    ): bool {
        return $this->sendUnzerOrderMail(
            $order,
            $toOwner,
            $this->unzerCancelTplHtml,
            $this->unzerCancelTplPlain,
            $toOwner ? 'OSCUNZER_CANCEL_MAIL_SUBJECT_OWNER' : 'OSCUNZER_CANCEL_MAIL_SUBJECT',
            [
                'unzerRefundedAmount' => $refundedAmount,
                'unzerCurrencyCode' => $currency,
            ]
        );
    }

    /**
     * @param Order $order
     * @param bool $toOwner
     * @param string $htmlTemplate
     * @param string $plainTemplate
     * @param string $subjectIdent language ident, receives the order number
     * @param array<string, mixed> $viewData additional template variables
     * @return bool
     */
    protected function sendUnzerOrderMail(
        Order $order,
        bool $toOwner,
        string $htmlTemplate,
        string $plainTemplate,
        string $subjectIdent,
        array $viewData
    ): bool {
        $shop = $this->_getShop();
        $this->_setMailParams($shop);

        $this->setViewData('order', $order);
        $this->setViewData('currency', $order->getOrderCurrency());
        $this->setViewData('isUnzerOwnerMail', $toOwner);
        foreach ($viewData as $name => $value) {
            $this->setViewData($name, $value);
        }

        $renderer = $this->getRenderer();

        // Process view data array through oxOutput processor
        $this->_processViewArray();

        $this->setBody($renderer->renderTemplate($htmlTemplate, $this->getViewData()));
        $this->setAltBody($renderer->renderTemplate($plainTemplate, $this->getViewData()));

        /** @var string $subject */
        $subject = Registry::getLang()->translateString($subjectIdent);
        $this->setSubject(sprintf($subject, $this->unzerFieldAsString($order, 'oxordernr')));

        if ($toOwner) {
            $this->setRecipient(
                $this->unzerFieldAsString($shop, 'oxowneremail'),
                $shop->oxshops__oxname->getRawValue()
            );

            return $this->send();
        }

        $fullName = $order->oxorder__oxbillfname->getRawValue()
            . ' ' . $order->oxorder__oxbilllname->getRawValue();

        $this->setRecipient($this->unzerFieldAsString($order, 'oxbillemail'), $fullName);
        $this->setReplyTo(
            $this->unzerFieldAsString($shop, 'oxorderemail'),
            $shop->oxshops__oxname->getRawValue()
        );

        return $this->send();
    }

    /**
     * getFieldData() is untyped, so anything that is not a plain value yields an
     * empty string instead of being cast.
     *
     * @param \OxidEsales\Eshop\Core\Model\BaseModel $model
     * @param string $field
     * @return string
     */
    protected function unzerFieldAsString($model, string $field): string
    {
        $value = $model->getFieldData($field);

        return is_scalar($value) ? (string)$value : '';
    }
}
