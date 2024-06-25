<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Controller;

use Exception;
use OxidEsales\Eshop\Application\Controller\AccountController;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidSolutionCatalysts\Unzer\Exception\UnzerException;
use OxidSolutionCatalysts\Unzer\Service\DebugHandler;
use OxidSolutionCatalysts\Unzer\Service\Transaction;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use OxidEsales\Eshop\Core\Registry;
use UnzerSDK\Exceptions\UnzerApiException;

class AccountSavedPaymentController extends AccountController
{
    use ServiceContainer;

    public function render()
    {
        parent::render();
        $this->redirectAfterLogin();

        $user = $this->getUser();
        if ($user && $user->getFieldData('oxpassword')) {
            $this->setPaymentListsToView();
            return "modules/osc/unzer/account_saved_payments.tpl";
        }
        return  $this->_sThisLoginTemplate;
    }

    protected function setPaymentListsToView(): void
    {
        $transactionService = $this->getServiceFromContainer(Transaction::class);
        $ids = $transactionService->getTrancactionIds($this->getUser());
        $paymentTypes = false;
        foreach ($ids as $typeData) {
            $paymentTypeId = $typeData['PAYMENTTYPEID'];
            $paymentId = (string)$typeData['OXPAYMENTTYPE'];
            $currency = $typeData['CURRENCY'];
            $customerType = $typeData['CUSTOMERTYPE'];
            $transactionOxId = $typeData['OXID'];

            if (empty($paymentTypeId)) {
                continue;
            }

            try {
                $unzerSDK = $this->getServiceFromContainer(UnzerSDKLoader::class)->getUnzerSDK(
                    $paymentId,
                    $currency,
                    $customerType
                );
                $paymentType = $unzerSDK->fetchPaymentType($paymentTypeId);
                if (strpos($paymentTypeId, 'crd') && method_exists($paymentType, 'getBrand')) {
                    $paymentTypes[$paymentType->getBrand()][$transactionOxId] = $paymentType->expose();
                }
                if (strpos($paymentTypeId, 'ppl')) {
                    $paymentTypes['paypal'][$transactionOxId] = $paymentType->expose();
                }
                if (strpos($paymentTypeId, 'sdd')) {
                    $paymentTypes['sepa'][$transactionOxId] = $paymentType->expose();
                }
            } catch (UnzerApiException | UnzerException | \Throwable $e) {
                if ($e->getCode() !== 'API.500.100.001') {
                    $logEntry = sprintf(
                        'Unknown error code while creating the PaymentList: "%s", message: "%s" ',
                        $e->getCode(),
                        $e->getMessage()
                    );
                    $logger = $this->getServiceFromContainer(DebugHandler::class);
                    $logger->log($logEntry);
                    continue;
                }
                $paymentTypes['invalid_payment_method_with_id'][$transactionOxId] = $paymentTypeId;
            }
        }

        $this->_aViewData['unzerPaymentType'] = $paymentTypes;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function deletePayment(): void
    {
        $paymentTypeId = Registry::getRequest()->getRequestParameter('paymenttypeid', '');
        $paymentTypeId = is_string($paymentTypeId) ? $paymentTypeId : '';
        /** @var \OxidSolutionCatalysts\Unzer\Model\Transaction $transaction */
        $transaction = oxNew(\OxidSolutionCatalysts\Unzer\Model\Transaction::class);
        $transaction->load($paymentTypeId);
        $transaction->setPaymentTypeId(null);
        $transaction->save();
    }
}
