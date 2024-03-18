<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Controller;

use OxidEsales\Eshop\Application\Controller\AccountController;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use OxidEsales\Eshop\Core\Registry;

class AccountSavedPaymentController extends AccountController
{
    use ServiceContainer;

    /**
     * @var string
     */
    // phpcs:ignore PSR2.Classes.PropertyDeclaration
    protected $_sThisLoginTemplate = 'page/account/login';

    /**
     * @var string
     */
    // phpcs:ignore PSR2.Classes.PropertyDeclaration
    protected $_sThisTemplate = '@osc-unzer/frontend/tpl/account/account_saved_payments';

    /**
     * @var string
     */
    // phpcs:ignore PSR2.Classes.PropertyDeclaration
    protected $sThisAltTemplate = 'page/privatesales/login';


    public function render()
    {
        parent::render();
        $this->redirectAfterLogin();

        $user = $this->getUser();
        if (!$user) {
            return $this->_sThisLoginTemplate;
        }
        $this->setPaymentListsToView();
        return $this->_sThisTemplate;
    }

    protected function setPaymentListsToView(): void
    {
        $UnzerSdk = $this->getServiceFromContainer(UnzerSDKLoader::class);
        $unzerSDK = $UnzerSdk->getUnzerSDK();

        $ids = $this->getTransactionIds();
        $paymentTypes = false;
        foreach ($ids as $typeId) {
            if (empty($typeId['PAYMENTTYPEID'])) {
                continue;
            }
            $paymentType = $unzerSDK->fetchPaymentType($typeId['PAYMENTTYPEID']);

            if (strpos($typeId['PAYMENTTYPEID'], 'crd') && method_exists($paymentType, 'getBrand')) {
                $paymentTypes[$paymentType->getBrand()][$typeId['OXID']] = $paymentType->expose();
            }
            if (strpos($typeId['PAYMENTTYPEID'], 'ppl')) {
                $paymentTypes['paypal'][$typeId['OXID']] = $paymentType->expose();
            }
            if (strpos($typeId['PAYMENTTYPEID'], 'sdd')) {
                $paymentTypes['sepa'][$typeId['OXID']] = $paymentType->expose();
            }
        }

        $this->_aViewData['unzerPaymentType'] = $paymentTypes;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function getTransactionIds(): array
    {
        $result = [];
        if ($this->getUser()) {
            $oDB = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
            $result = $oDB->getAll(
                "SELECT OXID, PAYMENTTYPEID from oscunzertransaction
                           where OXUSERID = :oxuserid
                             AND PAYMENTTYPEID IS NOT NULL
                           GROUP BY PAYMENTTYPEID ",
                [':oxuserid' => $this->getUser()->getId()]
            );
        }
        return $result;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function deletePayment(): void
    {
        $paymenttypeid = Registry::getRequest()->getRequestParameter('paymenttypeid');
        $oDB = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
        $oDB->getAll(
            "UPDATE oscunzertransaction
                SET PAYMENTTYPEID = NULL
                WHERE OXUSERID = :oxuserid AND OXID = :oxid",
            [':oxuserid' => $this->getUser()->getId(), 'oxid' => $paymenttypeid]
        );
    }
}
