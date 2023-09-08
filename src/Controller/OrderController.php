<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Controller;

use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\ArticleInputException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\NoArticleException;
use OxidEsales\Eshop\Core\Exception\OutOfStockException;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Exception\Redirect;
use OxidSolutionCatalysts\Unzer\Model\Payment;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use OxidSolutionCatalysts\Unzer\Service\Payment as PaymentService;
use OxidSolutionCatalysts\Unzer\Service\ResponseHandler;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Service\Unzer;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use OxidSolutionCatalysts\Unzer\Service\UnzerDefinitions;
use OxidSolutionCatalysts\Unzer\Core\UnzerDefinitions as CoreUnzerDefinitions;

/**
 * TODO: Decrease count of dependencies to 13
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class OrderController extends OrderController_parent
{
    use ServiceContainer;

    /**
     * @var bool $blSepaMandateConfirmError
     */
    protected $blSepaMandateConfirmError = null;

    /** @var Order $actualOrder */
    protected $actualOrder = null;

    /** @var array $companyTypes */
    protected $companyTypes = null;

    /**
     * @inerhitDoc
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function render()
    {
        $lang = Registry::getLang();

        /** @var int $iLang */
        $iLang = $lang->getBaseLanguage();
        $sLang = $lang->getLanguageAbbr($iLang);
        $this->_aViewData['unzerLocale'] = $sLang;

        // generate always a new threat metrix session id
        $unzer = $this->getServiceFromContainer(Unzer::class);
        $this->_aViewData['unzerThreatMetrixSessionID'] = $unzer->generateUnzerThreatMetrixIdInSession();
        return parent::render();
    }

    /**
     * @inerhitDoc
     */
    public function execute()
    {
        if (!$this->isSepaConfirmed()) {
            return '';
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
            /** @var \OxidSolutionCatalysts\Unzer\Model\Order $order */
            $order = $this->getActualOrder();
            $order->markUnzerOrderAsPaid();
        }

        return $ret;
    }

    /**
     * @throws Redirect
     * @throws DatabaseErrorException
     */
    public function unzerExecuteAfterRedirect(): void
    {
        // get basket contents
        $oUser = $this->getUser();
        $oBasket = $this->getSession()->getBasket();
        if ($oBasket->getProductsCount()) {

            $oDB = DatabaseProvider::getDb();

            /** @var \OxidSolutionCatalysts\Unzer\Model\Order $oOrder */
            $oOrder = $this->getActualOrder();

            $oDB->startTransaction();

            //finalizing ordering process (validating, storing order into DB, executing payment, setting status ...)
            $iSuccess = (int)$oOrder->finalizeUnzerOrderAfterRedirect($oBasket, $oUser);

            // performing special actions after user finishes order (assignment to special user groups)
            $oUser->onOrderExecute($oBasket, $iSuccess);

            $nextStep = $this->_getNextStep($iSuccess);

            if ('thankyou' ===  $nextStep) {
                $oDB->commitTransaction();
            }
            else {
                $oDB->rollbackTransaction();
            }

            // proceeding to next view
            $unzerService = $this->getServiceFromContainer(Unzer::class);
            throw new Redirect($unzerService->prepareRedirectUrl($nextStep));
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
        ($payment instanceof Payment) ?
            ( $payment->getId() === CoreUnzerDefinitions::SEPA_UNZER_PAYMENT_ID
            || $payment->getId() === CoreUnzerDefinitions::SEPA_SECURED_UNZER_PAYMENT_ID) : false
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
        /** @var \OxidSolutionCatalysts\Unzer\Model\Order $order */
        $order = $this->getActualOrder();
        $order->initWriteTransactionToDB();
    }

    /**
     * @return mixed|string
     */
    public function getApplePayLabel()
    {
        return $this->getServiceFromContainer(ModuleSettings::class)->getApplePayLabel();
    }

    /**
     * @return array
     */
    public function getSupportedApplepayMerchantCapabilities(): array
    {
        return $this->getServiceFromContainer(ModuleSettings::class)->getActiveApplePayMerchantCapabilities();
    }

    /**
     * @return array
     */
    public function getSupportedApplePayNetworks(): array
    {
        return $this->getServiceFromContainer(ModuleSettings::class)->getActiveApplePayNetworks();
    }

    /**
     * @return string
     */
    public function getUserCountryIso(): string
    {
        $country = oxNew(Country::class);
        /** @var string $oxcountryid */
        $oxcountryid = Registry::getSession()->getUser()->getFieldData('oxcountryid');
        $country->load($oxcountryid);

        /** @var string $oxisoalpha2 */
        $oxisoalpha2 = $country->getFieldData('oxisoalpha2');
        return $oxisoalpha2;
    }

    /**
     * @return Order
     */
    public function getActualOrder(): Order
    {
        if (!($this->actualOrder instanceof \OxidSolutionCatalysts\Unzer\Model\Order)) {
            $this->actualOrder = oxNew(Order::class);
            /** @var string $sess_challenge */
            $sess_challenge = Registry::getSession()->getVariable('sess_challenge');
            $this->actualOrder->load($sess_challenge);
        }
        return $this->actualOrder;
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getUnzerCompanyTypes(): array
    {
        if (empty($this->companyTypes)) {
            $this->companyTypes = [];
            $translator = $this->getServiceFromContainer(Translator::class);
            $unzerDefinitions = $this->getServiceFromContainer(UnzerDefinitions::class);

            foreach ($unzerDefinitions->getCompanyTypes() as $value) {
                $this->companyTypes[$value] = $translator->translate('OSCUNZER_COMPANY_FORM_' . $value);
            }
        }
        return $this->companyTypes;
    }

    /**
     * execute Unzer defined via getExecuteFnc
     */
    public function executeoscunzer(): ?string
    {
        $paymentService = $this->getServiceFromContainer(PaymentService::class);
        /** @var \OxidEsales\Eshop\Application\Model\Payment $payment */
        $payment = $this->getPayment();
        $paymentService->executeUnzerPayment2($payment);

        return null;
    }

    /**
     * OXID-Core
     * @inheritDoc
     */
    public function getExecuteFnc()
    {
        $payment = $this->getPayment();
        if (
            $payment->isUnzerPayment()
        ) {
            return 'executeoscunzer';
        }
        return parent::getExecuteFnc();
    }
}
