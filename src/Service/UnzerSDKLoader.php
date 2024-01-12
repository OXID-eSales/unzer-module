<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidSolutionCatalysts\Unzer\Core\UnzerDefinitions;
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
     * @param string $customerType
     * @param string $currency
     * @return Unzer
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getUnzerSDK(string $customerType = '', string $currency = '', bool $type = false): Unzer
    {
        if ($customerType !== '' && $currency !== '') {
            return $this->getUnzerSDKbyCustomerTypeAndCurrency($customerType, $currency, $type);
        }
        $key = $this->moduleSettings->getShopPrivateKey();
        $sdk = oxNew(Unzer::class, $key);
        if ($this->moduleSettings->isDebugMode()) {
            $sdk->setDebugMode(true)->setDebugHandler($this->debugHandler);
        }
        return $sdk;
    }

    /**
     * Will return a Unzer SDK object using a specific key, depending on $customerType and $currency.
     * Relevant for PaylaterInvoice. If $customerType or $currency is empty, the regular key is used.
     * @param string $customerType
     * @param string $currency
     * @return Unzer
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getUnzerSDKbyCustomerTypeAndCurrency(string $customerType, string $currency, bool $type): Unzer
    {
        if ($customerType == '' || $currency == '') {
            return $this->getUnzerSDK();
        }
        if ($type === false) {
            $key = $this->moduleSettings->getShopPrivateKeyInvoiceByCustomerTypeAndCurrency(
                $customerType,
                $currency
            );
        } else {
            $key = $this->moduleSettings->getShopPrivateKeyInstallmentByCustomerTypeAndCurrency(
                $customerType,
                $currency
            );
        }
        $sdk = oxNew(Unzer::class, $key);

        if ($this->moduleSettings->isDebugMode()) {
            $sdk->setDebugMode(true)->setDebugHandler($this->debugHandler);
        }
        return $sdk;
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
        if ($row) {
            $currency = $row['CURRENCY'];
            $paymentType = $row['OXPAYMENTTYPE'];
            if ($paymentType == UnzerDefinitions::INVOICE_UNZER_PAYMENT_ID) {
                $customerType = 'B2C';
                if (!empty($row['OXDELCOMPANY']) || !empty($row['OXBILLCOMPANY'])) {
                    $customerType = 'B2B';
                }
            }
            if ($paymentType === UnzerDefinitions::INSTALLMENT_UNZER_PAYLATER_PAYMENT_ID) {
                $customerType = 'B2C';
            }
        }
        return $this->getUnzerSDK($customerType, $currency);
    }
}
