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
    protected $_sThisLoginTemplate = 'page/account/login.tpl';


    /**
     * @var string
     */
    protected $_sThisAltLoginTemplate = 'page/privatesales/login.tpl';


    public function render()
    {
        parent::render();
        $this->redirectAfterLogin();

        $user = $this->getUser();
        $passwordField = 'oxuser__oxpassword';
        if ( $user || ($user && $user->$passwordField->value) ) {
            $this->setPaymentListsToView();
            return "modules/osc/unzer/account_saved_payments.tpl";
        }
        return  $this->_sThisLoginTemplate;
    }

    protected function setPaymentListsToView()
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

            if (strpos($typeId['PAYMENTTYPEID'], 'crd')) {
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


    protected function getTransactionIds()
    {
       if ($this->getUser()) {
           $oDB = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
           $rowSelect = $oDB->getAll("SELECT OXID, PAYMENTTYPEID from oscunzertransaction
                            where OXUSERID = :oxuserid AND PAYMENTTYPEID IS NOT NULL GROUP BY PAYMENTTYPEID ", [':oxuserid' => $this->getUser()->getId()]);

           return $rowSelect;
       }
        return false;
    }

    public function deletePayment()
    {
        $paymenttypeid = Registry::getRequest()->getRequestParameter('paymenttypeid');
        $oDB = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
        $oDB->getAll("UPDATE oscunzertransaction SET PAYMENTTYPEID = NULL
                           WHERE OXUSERID = :oxuserid AND OXID = :oxid",
            [':oxuserid' => $this->getUser()->getId(), 'oxid' => $paymenttypeid]);
    }



}
