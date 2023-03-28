<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Service;

use Doctrine\DBAL\Exception;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Core\Registry;
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
     */
    public function __construct(
        ModuleSettings $moduleSettings,
        DebugHandler $debugHandler,
        Session $session
    ) {
        $this->moduleSettings = $moduleSettings;
        $this->debugHandler = $debugHandler;
        $this->session = $session;
    }

    /**
     * @param string $customerType
     * @param string $currency
     * @return Unzer
     */
    public function getUnzerSDK(string $customerType = '', string $currency = ''): Unzer
    {
        if ($customerType != '' && $currency != '') {
            return $this->getUnzerSDKbyCustomerTypeAndCurrency($customerType, $currency);
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
     */
    public function getUnzerSDKbyCustomerTypeAndCurrency(string $customerType, string $currency): Unzer
    {
        if ($customerType == '' || $currency == '') {
            return $this->getUnzerSDK();
        }

        $key = $this->moduleSettings->getShopPrivateKeyInvoiceByCustomerTypeAndCurrency($customerType, $currency);
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
                if (empty($row['OXDELCOMPANY']) && empty($row['OXBILLCOMPANY'])) {
                    $customerType = 'B2C';
                }
                else {
                    $customerType = 'B2B';
                }
            }
        }
        return $this->getUnzerSDK($customerType, $currency);
    }
}
