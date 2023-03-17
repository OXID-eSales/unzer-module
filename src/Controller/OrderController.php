<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Controller;

use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Exception\ArticleInputException;
use OxidEsales\Eshop\Core\Exception\NoArticleException;
use OxidEsales\Eshop\Core\Exception\OutOfStockException;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Exception\Redirect;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use OxidSolutionCatalysts\Unzer\Service\ResponseHandler;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Service\Unzer;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use OxidSolutionCatalysts\Unzer\Core\UnzerDefinitions;

class OrderController extends OrderController_parent
{
    use ServiceContainer;

    protected $blSepaMandateConfirmError = null;

    protected $actualOrder = null;

    protected $companyTypes = null;

    /**
     * @inerhitDoc
     */
    public function execute()
    {
        if (!$this->isSepaConfirmed()) {
            return;
        }

        $ret = parent::execute();

        if ($ret && str_starts_with($ret, 'thankyou')) {
            $this->saveUnzerTransaction();
        }

        $unzer = $this->getServiceFromContainer(Unzer::class);
        if ($unzer->isAjaxPayment()) {
            $response = $this->getServiceFromContainer(ResponseHandler::class)->response();
            if ($ret && !str_contains($ret, 'thankyou')) {
                $response->setUnauthorized()->sendJson();
            }

            $response->setData([
                'redirectUrl' => $unzer->prepareRedirectUrl('thankyou')
            ])->sendJson();
        } elseif ($this->isSepaPayment()) {
            $this->getActualOrder()->markUnzerOrderAsPaid();
        }

        return $ret;
    }

    /**
     * @throws Redirect
     */
    public function unzerExecuteAfterRedirect(): void
    {
        // get basket contents
        $oUser = $this->getUser();
        $oBasket = $this->getSession()->getBasket();
        if ($oBasket->getProductsCount()) {
            try {
                $oOrder = $this->getActualOrder();

                //finalizing ordering process (validating, storing order into DB, executing payment, setting status ...)
                $iSuccess = (int)$oOrder->finalizeUnzerOrderAfterRedirect($oBasket, $oUser);

                // performing special actions after user finishes order (assignment to special user groups)
                $oUser->onOrderExecute($oBasket, $iSuccess);

                $nextStep = $this->_getNextStep($iSuccess);

                // proceeding to next view
                $unzerService = $this->getServiceFromContainer(Unzer::class);
                throw new Redirect($unzerService->prepareRedirectUrl($nextStep));
            } catch (OutOfStockException $oEx) {
                $oEx->setDestination('basket');
                Registry::getUtilsView()->addErrorToDisplay($oEx, false, true, 'basket');
            } catch (NoArticleException | ArticleInputException $oEx) {
                Registry::getUtilsView()->addErrorToDisplay($oEx);
            }
        }
    }

    /**
     * @return bool|null
     */
    public function isSepaMandateConfirmationError()
    {
        return $this->blSepaMandateConfirmError;
    }

    /**
     * @return bool|null
     */
    public function isSepaPayment(): ?bool
    {
        $payment = $this->getPayment();

        return (
        is_object($payment) ?
            ( $payment->getId() === UnzerDefinitions::SEPA_UNZER_PAYMENT_ID
            || $payment->getId() === UnzerDefinitions::SEPA_SECURED_UNZER_PAYMENT_ID) : false
        );
    }

    /**
     * @return bool|null
     */
    public function isSepaConfirmed(): ?bool
    {
        if ($this->isSepaPayment()) {
            $blSepaMandateConfirm = Registry::getRequest()->getRequestParameter('sepaConfirmation');
            if (!$blSepaMandateConfirm) {
                $this->blSepaMandateConfirmError = true;
                return false;
            }
        }
        return true;
    }

    /**
     * @return void
     */
    public function saveUnzerTransaction(): void
    {
        $this->getActualOrder()->initWriteTransactionToDB();
    }

    public function getApplePayLabel()
    {
        return $this->getServiceFromContainer(ModuleSettings::class)->getApplePayLabel();
    }

    public function getSupportedApplepayMerchantCapabilities(): array
    {
        return $this->getServiceFromContainer(ModuleSettings::class)->getActiveApplePayMerchantCapabilities();
    }

    public function getSupportedApplePayNetworks(): array
    {
        return $this->getServiceFromContainer(ModuleSettings::class)->getActiveApplePayNetworks();
    }

    public function getUserCountryIso(): string
    {
        $country = oxNew(Country::class);
        $country->load(Registry::getSession()->getUser()->oxuser__oxcountryid->value);

        return $country->oxcountry__oxisoalpha2->value;
    }

    public function getActualOrder(): Order
    {
        if (is_null($this->actualOrder)) {
            $this->actualOrder = oxNew(Order::class);
            $this->actualOrder->load(Registry::getSession()->getVariable('sess_challenge'));
        }
        return $this->actualOrder;
    }


    public function getUnzerCompanyTypes(): array
    {
        if (is_null($this->companyTypes) || empty($this->companyTypes)) {
            $this->companyTypes = [];
            $translator = $this->getServiceFromContainer(Translator::class);
            foreach (UnzerDefinitions::getUnzerCompanyTypes() as $value) {
                $this->companyTypes[$value] = $translator->translate('OSCUNZER_COMPANY_FORM_' . $value);
            }
        }
        return $this->companyTypes;
    }
}
