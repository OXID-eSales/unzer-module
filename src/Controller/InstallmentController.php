<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Exception\Redirect;
use OxidSolutionCatalysts\Unzer\Service\Transaction;
use OxidSolutionCatalysts\Unzer\Service\Unzer;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Resources\PaymentTypes\InstallmentSecured;

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


    public function render()
    {
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
     * Template variable getter. Returns execution function name
     *
     * @return string
     */
    public function getExecuteFnc()
    {
        return 'confirmInstallment';
    }

    protected function getUnzerSessionPayment()
    {
        if ($this->uzrPayment === null) {
            $this->uzrPayment = $this->getServiceFromContainer(
                \OxidSolutionCatalysts\Unzer\Service\Payment::class
            )->getSessionUnzerPayment();
        }
        return $this->uzrPayment;
    }

    public function cancelInstallment()
    {
        $paymentService = $this->getServiceFromContainer(\OxidSolutionCatalysts\Unzer\Service\Payment::class);
        $paymentService->removeTemporaryOrder();

        $unzerService = $this->getServiceFromContainer(Unzer::class);

        throw new Redirect($unzerService->prepareRedirectUrl('payment'));
    }

    public function confirmInstallment()
    {
        $unzerPayment = $this->getUnzerSessionPayment();
        $oOrder = oxNew(Order::class);

        if ($oOrder->load(Registry::getSession()->getVariable('sess_challenge'))) {
            $charge = $unzerPayment->getAuthorization()->charge();

            $transactionService = $this->getServiceFromContainer(Transaction::class);
            $transactionService->writeChargeToDB(
                $oOrder->getId(),
                $oOrder->oxorder__oxuserid->value,
                $charge
            );
            if ($charge->isSuccess() && $charge->getPayment()->getAmount()->getRemaining() == 0) {
                $oOrder->markUnzerOrderAsPaid();
            }

            $unzerService = $this->getServiceFromContainer(Unzer::class);
            throw new Redirect($unzerService->prepareRedirectUrl('order&fnc=unzerExecuteAfterRedirect&pdfConfirm=1'));
        }
    }
}
