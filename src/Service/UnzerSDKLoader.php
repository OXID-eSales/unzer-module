<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidSolutionCatalysts\Unzer\Core\UnzerDefinitions;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use UnzerSDK\Unzer;

class UnzerSDKLoader
{
    /**
     * @var ModuleSettings
     */
    private $moduleSettings;

    /**
     * @var DebugHandler
     */
    private $debugHandler;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param ModuleSettings $moduleSettings
     * @param DebugHandler $debugHandler
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function __construct(
        ModuleSettings $moduleSettings,
        DebugHandler $debugHandler,
        Session $session
    ) {
        $this->moduleSettings = $moduleSettings;
        $this->debugHandler = $debugHandler;
        $this->session = $session;
        $ignore = $this->session->isAdmin();
    }

    /**
     * @param string $paymentId
     * @param string $currency
     * @param string $customerType
     * @return Unzer
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getUnzerSDK(string $paymentId = '', string $customerType = '', string $currency = ''): Unzer
    {
        if (UnzerDefinitions::INVOICE_UNZER_PAYMENT_ID === $paymentId) {
            $key = $this->moduleSettings->getInvoicePrivateKeyByCustomerTypeAndCurrency(
                $customerType,
                $currency
            );
        } elseif (UnzerDefinitions::INSTALLMENT_UNZER_PAYLATER_PAYMENT_ID === $paymentId) {
            $key = $this->moduleSettings->getInstallmentPrivateKeyByCurrency(
                $currency
            );
        } else {
            $key = $this->moduleSettings->getStandardPrivateKey();
        }

        return $this->getUnzerSDKbyKey($key);
    }

    /**
     * Creates an UnzerSDK object based upon a specific private key.
     * @param string $key
     * @return Unzer
     */
    public function getUnzerSDKbyKey(string $key): Unzer
    {
        $sdk = oxNew(Unzer::class, $key);
        if ($this->moduleSettings->isDebugMode()) {
            $sdk->setDebugMode(true)->setDebugHandler($this->debugHandler);
        }
        return $sdk;
    }

    /**
     * Initialize UnzerSDK from a payment id
     * @param string $sPaymentId
     * @return Unzer
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getUnzerSDKbyPaymentType(string $sPaymentId): Unzer
    {
        $oDB = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
        $row = $oDB->getRow("SELECT u.CURRENCY, o.OXDELCOMPANY, o.OXBILLCOMPANY, o.OXPAYMENTTYPE
                            FROM oscunzertransaction u
                            LEFT JOIN oxorder o ON u.OXORDERID = o.OXID
                            WHERE u.TYPEID = :typeid
                            ORDER BY u.OXTIMESTAMP DESC LIMIT 1", [':typeid' => $sPaymentId]);

        $customerType = '';
        $currency = '';
        $paymentId = '';
        if ($row) {
            $currency = $row['CURRENCY'];
            $paymentId = $row['OXPAYMENTTYPE'];
            if ($paymentId === UnzerDefinitions::INVOICE_UNZER_PAYMENT_ID) {
                $customerType = 'B2C';
                if (!empty($row['OXDELCOMPANY']) || !empty($row['OXBILLCOMPANY'])) {
                    $customerType = 'B2B';
                }
            }
        }

        return $this->getUnzerSDK($paymentId, $currency, $customerType);
    }
}
