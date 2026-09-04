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
        // The customer is written to in the language they ordered in. The shop owner
        // keeps the language the backend is running in, because that copy is read
        // next to the order there - so only the customer mail switches the language.
        // Core\Email::sendSendedNowMail() handles its backend triggered mail the
        // same way, including loading the shop in that language: the shop name and
        // the sender texts are translatable too.
        $mailLanguage = $toOwner ? null : $this->unzerOrderLanguage($order);

        $shop = $mailLanguage === null ? $this->_getShop() : $this->_getShop($mailLanguage);
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

        $lang = Registry::getLang();
        $previousTplLanguage = (int)$lang->getTplLanguage();
        $previousBaseLanguage = (int)$lang->getBaseLanguage();
        if ($mailLanguage !== null) {
            $lang->setTplLanguage($mailLanguage);
            $lang->setBaseLanguage($mailLanguage);
        }

        // These mails are triggered from the backend, but they use frontend
        // templates and frontend language files. Rendering them in admin mode
        // leaves core idents unresolved ("ERROR: Translation for ORDER_NUMBER not
        // found!"), so switch the admin mode off around the rendering and restore
        // whatever it was before.
        $config = Registry::getConfig();
        $wasAdmin = $config->isAdmin();
        $config->setAdminMode(false);

        try {
            $this->setBody($renderer->renderTemplate($htmlTemplate, $this->getViewData()));
            $this->setAltBody($renderer->renderTemplate($plainTemplate, $this->getViewData()));

            // the subject ident lives in the frontend language files, so it belongs
            // into the same window as the templates
            /** @var string $subject */
            $subject = $lang->translateString($subjectIdent);
            $this->setSubject(sprintf($subject, $this->unzerFieldAsString($order, 'oxordernr')));
        } finally {
            // A failing template must not leave the shop behind in frontend mode or
            // in the order language: the admin page that triggered the mail is
            // rendered after this and would lose its templates and translations.
            $config->setAdminMode($wasAdmin);
            if ($mailLanguage !== null) {
                $lang->setTplLanguage($previousTplLanguage);
                $lang->setBaseLanguage($previousBaseLanguage);
            }
        }

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
     * Language the order was placed in. getFieldData() is untyped, so anything
     * that is not a number falls back to the shop default language.
     *
     * @param Order $order
     * @return int
     */
    protected function unzerOrderLanguage(Order $order): int
    {
        $language = $order->getFieldData('oxlang');

        return is_numeric($language) ? (int)$language : 0;
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
