<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Controller;

use Exception;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Exception\Redirect;
use OxidSolutionCatalysts\Unzer\Exception\RedirectWithMessage;
use OxidSolutionCatalysts\Unzer\Model\Payment;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use OxidSolutionCatalysts\Unzer\Service\Payment as PaymentService;
use OxidSolutionCatalysts\Unzer\Service\ResponseHandler;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Service\Unzer;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use OxidSolutionCatalysts\Unzer\Service\UnzerDefinitions;
use OxidSolutionCatalysts\Unzer\Core\UnzerDefinitions as CoreUnzerDefinitions;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * TODO: Decrease count of dependencies to 13
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
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

        $basketHashBefore = $this->getBasketHash();
        Registry::getSession()->setVariable('unzerBasketHashBefore', $basketHashBefore);

        /** @var int $iLang */
        $iLang = $lang->getBaseLanguage();
        $sLang = $lang->getLanguageAbbr($iLang);
        $this->_aViewData['unzerLocale'] = $sLang;

        // generate always a new threat metrix session id
        $unzer = $this->getServiceFromContainer(Unzer::class);
        $this->_aViewData['unzerThreatMetrixSessionID'] = $unzer->generateUnzerThreatMetrixIdInSession();
        $this->_aViewData['uzrcurrency'] = $this->getActCurrency();

        $this->getSavedPayment();

        return parent::render();
    }

    /**
     * @inerhitDoc
     */
    public function execute()
    {
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
        }

        return $ret;
    }

    /**
     * @throws Redirect
     * @throws DatabaseErrorException
     * @throws Exception
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.ElseExpression)
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
            $unzerService = $this->getServiceFromContainer(Unzer::class);
            if (stripos($nextStep, 'thankyou') !== false) {
                $oDB->commitTransaction();
                $unzerPaymentId = Registry::getSession()->getVariable('paymentid');
                $paymentService = $this->getServiceFromContainer(PaymentService::class);
                if ($unzerService->ifImmediatePostAuthCollect($paymentService)) {
                    $paymentService->doUnzerCollect(
                        $oOrder,
                        $unzerPaymentId,
                        $oBasket->getDiscountedProductsBruttoPrice()
                    );
                }

                if ($oBasket->getPaymentId() !== CoreUnzerDefinitions::APPLEPAY_UNZER_PAYMENT_ID) {
                    throw new Redirect($unzerService->prepareRedirectUrl($nextStep));
                }
                // this action was called from js if payment was settled correctly with unzer apple pay,
                // and the order needs to be updated as successfully paid, we send here just a
                // 200 response, the next step is that the js calls the thank you page
                // modules/osc/unzer/views/frontend/tpl/order/unzer_applepay.tpl:77
                return;
            }

            $oDB->rollbackTransaction();
            $translator = $this->getServiceFromContainer(Translator::class);
            throw new RedirectWithMessage(
                $unzerService->prepareRedirectUrl($nextStep),
                $translator->translate('OSCUNZER_ERROR_DURING_CHECKOUT')
            );
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
            $payment instanceof Payment &&
            (
                $payment->getId() === CoreUnzerDefinitions::SEPA_UNZER_PAYMENT_ID ||
                $payment->getId() === CoreUnzerDefinitions::SEPA_SECURED_UNZER_PAYMENT_ID
            )
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
     * @return int|mixed
     */
    public function getPaymentSaveSetting()
    {
        $bSavedPayment = 1;

        // no guests allowed
        $user = $this->getUser();
        if (!$user || (!$user->getFieldData('oxpassword'))) {
            $bSavedPayment = 0;
            return $bSavedPayment;
        }
        return $bSavedPayment;
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
     *
     */
    public function executeoscunzer(): ?string
    {
        if (!$this->isSepaConfirmed()) {
            return null;
        }

        if (!$this->_validateTermsAndConditions()) {
            $this->_blConfirmAGBError = true;
            return null;
        }

        // validate Basket
        $basketHashAfter = $this->getBasketHash();
        $basketHashBefore = Registry::getSession()->getVariable('unzerBasketHashBefore');

        if ($basketHashBefore !== $basketHashAfter) {
            Registry::getUtilsView()->addErrorToDisplay(
                oxNew(
                    StandardException::class,
                    Registry::getLang()->translateString('oscunzer_ERROR_CHANGE_BASKET')
                )
            );
            return null;
        }

        $paymentService = $this->getServiceFromContainer(PaymentService::class);
        /** @var \OxidEsales\Eshop\Application\Model\Payment $payment */
        $payment = $this->getPayment();
        $paymentOk = $paymentService->executeUnzerPayment($payment);

        // all orders without redirect would be finalized now
        if ($paymentOk) {
            $this->unzerExecuteAfterRedirect();
        }

        return null;
    }

    /**
     * OXID-Core
     * @inheritDoc
     */
    public function getExecuteFnc()
    {
        /** @var Payment $payment */
        $payment = $this->getPayment();
        if (
            $payment->isUnzerPayment()
        ) {
            return 'executeoscunzer';
        }
        return parent::getExecuteFnc();
    }
    protected function getSavedPayment(): void
    {
        $UnzerSdk = $this->getServiceFromContainer(UnzerSDKLoader::class);
        $unzerSDK = $UnzerSdk->getUnzerSDK();

        $ids = $this->getTrancactionIds();
        $paymentTypes = false;
        if ($ids) {
            foreach ($ids as $typeId) {
                if (!empty($typeId['PAYMENTTYPEID'])) {
                    try {
                        $paymentType = $unzerSDK->fetchPaymentType($typeId['PAYMENTTYPEID']);
                    } catch (UnzerApiException $e) {
                        continue;
                    }

                    if (strpos($typeId['PAYMENTTYPEID'], 'crd')) {
                        $paymentTypes['card'][$typeId['PAYMENTTYPEID']] = $paymentType->expose();
                    }
                    if (strpos($typeId['PAYMENTTYPEID'], 'ppl')) {
                        $paymentTypes['paypal'][$typeId['PAYMENTTYPEID']] = $paymentType->expose();
                    }
                    if (strpos($typeId['PAYMENTTYPEID'], 'sdd')) {
                        $paymentTypes['sepa'][$typeId['PAYMENTTYPEID']] = $paymentType->expose();
                    }
                }
            }
        }

        $this->_aViewData['unzerPaymentType'] = $paymentTypes;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function getTrancactionIds(): array
    {
        $result = [];
        if ($this->getUser() && $this->getUser()->getId() !== null) {
            $oDB = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
            $result = $oDB->getAll(
                "SELECT PAYMENTTYPEID from oscunzertransaction
                where OXUSERID = :oxuserid AND PAYMENTTYPEID IS NOT NULL
                GROUP BY PAYMENTTYPEID ",
                [':oxuserid' => $this->getUser()->getId()]
            );
        }
        return $result;
    }

    protected function getBasketHash(): string
    {
        /** @var ?Basket $oBasket */
        $oBasket = $this->getBasket();
        if (!$oBasket) {
            return '';
        }
        $oBasket->calculateBasket();
        $oBasket->onUpdate();
        $basketSummery = $oBasket->getBasketSummary();
        $basketContents = $oBasket->getContents();
        return md5(
            serialize($basketSummery) .
            serialize($basketContents)
        );
    }
}
