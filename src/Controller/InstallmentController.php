<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Application\Model\Payment as PaymentModel;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Application\Model\Basket;
use OxidSolutionCatalysts\Unzer\Exception\Redirect;
use OxidSolutionCatalysts\Unzer\Service\Transaction;
use OxidSolutionCatalysts\Unzer\Service\Unzer;
use OxidSolutionCatalysts\Unzer\Service\Payment as PaymentService;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Resources\PaymentTypes\InstallmentSecured;
use UnzerSDK\Resources\TransactionTypes\Authorization;

class InstallmentController extends FrontendController
{
    use ServiceContainer;

    /**
     * Current class template name.
     *
     * @var string
     */
    // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore
    protected $_sThisTemplate = 'modules/osc/unzer/unzer_installment_confirm.tpl';

    /** @var Payment $uzrPayment */
    protected $uzrPayment;

    /** @var PaymentModel $oxPayment */
    protected $oxPayment;

    /**
     * @inheritDoc
     *
     * @return void
     */
    public function init()
    {
        $this->setIsOrderStep(true);
        parent::init();
    }

    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function render()
    {
        /** @var Basket $oBasket */
        $oBasket = Registry::getSession()->getBasket();
        /** @var User|null $oUser */
        $oUser = Registry::getSession()->getUser();

        $myConfig = Registry::getConfig();

        if (!$oBasket->getProductsCount()) {
            Registry::getUtils()->redirect($myConfig->getShopHomeUrl() . 'cl=basket', true, 302);
        }

        // can we proceed with ordering ?
        if (!$oUser && $oBasket->getProductsCount() > 0) {
            Registry::getUtils()->redirect($myConfig->getShopHomeUrl() . 'cl=basket', false, 302);
        } elseif (!$oUser || !$oBasket->getProductsCount()) {
            Registry::getUtils()->redirect($myConfig->getShopHomeUrl(), false, 302);
        }

        // payment is set ?
        if (!$this->getPayment()) {
            // redirecting to payment step on error ..
            Registry::getUtils()->redirect($myConfig->getShopCurrentURL() . '&cl=payment', true, 302);
        }

        /** @var string $sPdfLink */
        $sPdfLink = Registry::getSession()->getVariable('UzrPdfLink');
        if (empty($sPdfLink)) {
            // redirecting to payment step on error ..
            Registry::getUtils()->redirect($myConfig->getShopCurrentURL() . '&cl=payment', false, 302);
        }

        $this->_aViewData['sPdfLink'] = Registry::getSession()->getVariable('UzrPdfLink');

        $this->getUnzerSessionPayment();
        /** @var InstallmentSecured $uzrInstallment */
        $uzrInstallment = $this->uzrPayment->getPaymentType();

        $this->_aViewData['fTotal'] = $uzrInstallment->getTotalAmount();
        $this->_aViewData['fPruchaseAmount'] = $uzrInstallment->getTotalPurchaseAmount();
        $this->_aViewData['fInterestAmount'] = $uzrInstallment->getTotalInterestAmount();
        $this->_aViewData['uzrCurrency'] = $this->uzrPayment->getCurrency();
        $this->_aViewData['uzrRate'] = $uzrInstallment->getEffectiveInterestRate();

        parent::render();

        return $this->_sThisTemplate;
    }

    /**
     * Template variable getter. Returns payment object
     *
     * @return object|bool
     */
    public function getPayment()
    {
        if ($this->oxPayment === null) {
            $oBasket = Registry::getSession()->getBasket();
            $oUser = Registry::getSession()->getUser();

            // payment is set ?
            $sPaymentid = $oBasket->getPaymentId();
            $oPayment = oxNew(PaymentModel::class);

            /** @var string $sShipSet */
            $sShipSet = Registry::getSession()->getVariable('sShipSet');
            if (
                $sPaymentid && $oPayment->load($sPaymentid) &&
                $oPayment->isValidPayment(
                    (array)Registry::getSession()->getVariable('dynvalue'),
                    (string)$this->getConfig()->getShopId(),
                    $oUser,
                    $oBasket->getPriceForPayment(),
                    $sShipSet
                )
            ) {
                $this->oxPayment = $oPayment;
            }
        }

        return $this->oxPayment;
    }

    /**
     * Template variable getter. Returns execution function name
     *
     * @return string
     */
    public function getExecuteFnc()
    {
        return 'confirmInstallment';
    }

    /**
     * @return Payment|null
     */
    protected function getUnzerSessionPayment(): ?Payment
    {
        if ($this->uzrPayment === null) {
            /** @var \OxidSolutionCatalysts\Unzer\Service\Payment $payment */
            $payment = $this->getServiceFromContainer(
                PaymentService::class
            );
            /** @var Payment $sessionUnzerPayment */
            $sessionUnzerPayment = $payment->getSessionUnzerPayment();
            $this->uzrPayment = $sessionUnzerPayment;
        }
        return $this->uzrPayment;
    }

    /**
     * @return never
     */
    public function cancelInstallment()
    {
        $paymentService = $this->getServiceFromContainer(PaymentService::class);
        $paymentService->removeTemporaryOrder();

        $unzerService = $this->getServiceFromContainer(Unzer::class);

        throw new Redirect($unzerService->prepareRedirectUrl('payment?payerror=2'));
    }

    public function confirmInstallment(): void
    {
        /** @var Payment $unzerPayment */
        $unzerPayment = $this->getUnzerSessionPayment();
        /** @var \OxidSolutionCatalysts\Unzer\Model\Order $oOrder */
        $oOrder = oxNew(Order::class);
        /** @var string $sess_challenge */
        $sess_challenge = Registry::getSession()->getVariable('sess_challenge');

        if ($oOrder->load($sess_challenge)) {
            /** @var string $oxuserid */
            $oxuserid = $oOrder->getFieldData('oxuserid');
            /** @var Authorization $authorization */
            $authorization = $unzerPayment->getAuthorization();
            $charge = $authorization->charge();
            $transactionService = $this->getServiceFromContainer(Transaction::class);
            $transactionService->writeChargeToDB(
                $oOrder->getId(),
                $oxuserid,
                $charge
            );
            /** @var Payment $payment */
            $payment = $charge->getPayment();
            if ($charge->isSuccess() && $payment->getAmount()->getRemaining() == 0) {
                $oOrder->markUnzerOrderAsPaid();
            }

            $unzerService = $this->getServiceFromContainer(Unzer::class);
            throw new Redirect($unzerService->prepareRedirectUrl('order&fnc=unzerExecuteAfterRedirect&pdfConfirm=1'));
        }
    }
}
