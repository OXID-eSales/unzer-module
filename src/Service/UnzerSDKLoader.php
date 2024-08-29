<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Service;

use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Driver\ResultStatement;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidSolutionCatalysts\Unzer\Exception\UnzerException;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use RuntimeException;
use OxidSolutionCatalysts\Unzer\Core\UnzerDefinitions;
use UnzerSDK\Unzer;

class UnzerSDKLoader
{
    use ServiceContainer;

    /**
     * @var ModuleSettings
     */
    private $moduleSettings;

    /**
     * @var DebugHandler
     */
    private DebugHandler $debugHandler;


    /**
     * @param ModuleSettings $moduleSettings
     * @param DebugHandler $debugHandler
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function __construct(
        ModuleSettings $moduleSettings,
        DebugHandler $debugHandler
    ) {
        $this->moduleSettings = $moduleSettings;
        $this->debugHandler = $debugHandler;
    }

    /**
     * @param string $paymentId
     * @param string $currency
     * @param string $customerType
     * @return Unzer
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @throws UnzerException
     */
    public function getUnzerSDK(string $paymentId = '', string $currency = '', string $customerType = ''): Unzer
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

        try {
            $sdk = $this->getUnzerSDKbyKey($key);
        } catch (UnzerException $e) {
            $logEntry = sprintf(
                'Try to get the SDK with the Key "%s" defined by paymentId "%s", currency "%s", customerType "%s"',
                $key,
                $paymentId,
                $currency,
                $customerType
            );
            $this->debugHandler->log($logEntry);
            throw new UnzerException($logEntry);
        }

        return $sdk;
    }

    /**
     * Creates an UnzerSDK object based upon a specific private key.
     * @param string $key
     * @return Unzer
     * @throws UnzerException
     */
    public function getUnzerSDKbyKey(string $key): Unzer
    {
        try {
            $sdk = oxNew(Unzer::class, $key);
            if ($this->moduleSettings->isDebugMode()) {
                $sdk->setDebugMode(true)->setDebugHandler($this->debugHandler);
            }
        } catch (RuntimeException $e) {
            $this->debugHandler->log($e->getMessage());
            throw new UnzerException($e->getMessage());
        }
        return $sdk;
    }

    /**
     * Initialize UnzerSDK from a payment id
     * @param string $sPaymentId
     * @return Unzer
     *
     * @throws UnzerException
     * @throws Exception|\Doctrine\DBAL\Exception
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getUnzerSDKbyPaymentType(string $sPaymentId): Unzer
    {
        $queryBuilderFactory = $this->getServiceFromContainer(QueryBuilderFactoryInterface::class);
        $queryBuilder = $queryBuilderFactory->create();

        $query = $queryBuilder
            ->select(
                'u.currency',
                'o.oxdelcompany',
                'o.oxbillcompany',
                'o.oxpaymenttype'
            )
            ->from('oscunzertransaction', 'u')
            ->leftJoin('u', 'oxorder', 'o', 'u.oxorderid = o.oxid')
            ->where($queryBuilder->expr()->eq('u.typeid', ':typeid'))
            ->orderBy('u.oxtimestamp', 'desc')
            ->setMaxResults(1);

        $parameters = [
            ':typeid' => $sPaymentId,
        ];

        $result = $query->setParameters($parameters)->execute();
        $row = null;
        if ($result instanceof ResultStatement && $result->columnCount() === 1) {
            $row = $result->fetchAssociative();
        }

        $customerType = '';
        $currency = '';
        $paymentId = '';
        if ($row) {
            $currency = is_string($row['currency']) ? $row['currency'] : '';
            $paymentId = is_string($row['oxpaymenttype']) ? $row['oxpaymenttype'] : '';
            if ($paymentId === UnzerDefinitions::INVOICE_UNZER_PAYMENT_ID) {
                $customerType = 'B2C';
                if (!empty($row['oxdelcompany']) || !empty($row['oxbillcompany'])) {
                    $customerType = 'B2B';
                }
            }
            if ($paymentId === UnzerDefinitions::INSTALLMENT_UNZER_PAYLATER_PAYMENT_ID) {
                $customerType = 'B2C';
            }
        }

        return $this->getUnzerSDK((string)$paymentId, $currency, $customerType);
    }
}
