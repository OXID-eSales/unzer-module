<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Service;

use Exception;
use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\Basket as BasketModel;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\ShopVersion;
use OxidEsales\EshopCommunity\Core\Field as FieldAlias;
use OxidEsales\Facts\Facts;
use OxidSolutionCatalysts\Unzer\Core\UnzerDefinitions;
use UnzerSDK\Constants\CompanyRegistrationTypes;
use UnzerSDK\Resources\Basket;
use UnzerSDK\Constants\BasketItemTypes;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\CustomerFactory;
use UnzerSDK\Resources\EmbeddedResources\BasketItem;
use UnzerSDK\Resources\EmbeddedResources\CompanyInfo;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\PaymentTypes\Prepayment;
use UnzerSDK\Resources\TransactionTypes\AbstractTransactionType;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Resources\TransactionTypes\Charge;

/**
 * TODO: Decrease count of dependencies to 13
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Unzer
{
    /** @var Session */
    protected $session;

    /** @var Translator */
    protected $translator;

    /** @var Context */
    protected $context;

    /** @var ModuleSettings */
    protected $moduleSettings;

    /** @var Request */
    protected $request;

    /**
     * @param Session $session
     * @param Translator $translator
     * @param Context $context
     * @param ModuleSettings $moduleSettings
     * @param Request $request
     */
    public function __construct(
        Session $session,
        Translator $translator,
        Context $context,
        ModuleSettings $moduleSettings,
        Request $request
    ) {
        $this->session = $session;
        $this->translator = $translator;
        $this->context = $context;
        $this->moduleSettings = $moduleSettings;
        $this->request = $request;
    }

    /**
     * @param User $oUser
     * @param Order|null $oOrder
     * @param string $companyType
     * @return Customer
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getUnzerCustomer(
        User $oUser,
        ?Order $oOrder = null,
        string $companyType = ''
    ): Customer {
        /** @var string $oxfname */
        $oxfname = $oUser->getFieldData('oxfname');
        /** @var string $oxlname */
        $oxlname = $oUser->getFieldData('oxlname');
        $customer = CustomerFactory::createCustomer(
            $oxfname,
            $oxlname
        );

        $birthdate = Registry::getRequest()->getRequestParameter('birthdate');
        if (is_string($birthdate)) {
            $oUser->assign(['oxuser__oxbirthdate' => $birthdate]);
        }

        /** @var string $birthdate */
        $birthdate = $oUser->getFieldData('oxbirthdate');
        $customer->setBirthDate($birthdate != "0000-00-00" ? $birthdate : '');

        /** @var string $oxcompany */
        $oxcompany = $oUser->getFieldData('oxcompany');
        $customer->setCompany($oxcompany);

        /** @var string $oxsal */
        $oxsal = $oUser->getFieldData('oxsal');
        $customer->setSalutation($oxsal);

        /** @var string $oxusername */
        $oxusername = $oUser->getFieldData('oxusername');
        $customer->setEmail($oxusername);

        /** @var string $oxfon */
        $oxfon = $oUser->getFieldData('oxfon');
        $customer->setPhone($oxfon);

        /** @var string $oxmobfon */
        $oxmobfon = $oUser->getFieldData('oxmobfon');
        $customer->setMobile($oxmobfon);

        $billingAddress = $customer->getBillingAddress();

        $oCountry = oxNew(Country::class);
        /** @var string $oxcountryid */
        $oxcountryid = $oUser->getFieldData('oxcountryid');
        /** @var string $billingCountryIso */
        $billingCountryIso = $oCountry->load($oxcountryid)
            ? $oCountry->getFieldData('oxisoalpha2')
            : '';

        $billingAddress->setName(!empty($oxcompany) ? $oxcompany : $oxfname . ' ' . $oxlname);
        $billingAddress->setStreet(trim(
            $oUser->getFieldData('oxstreet') .
            ' ' .
            $oUser->getFieldData('oxstreetnr')
        ));

        /** @var string $oxzip */
        $oxzip = $oUser->getFieldData('oxzip');
        $billingAddress->setZip($oxzip);

        /** @var string $oxcity */
        $oxcity = $oUser->getFieldData('oxcity');
        $billingAddress->setCity(trim($oxcity));
        $billingAddress->setCountry($billingCountryIso);

        $oDelAddress = null;
        if ($oOrder) {
            $oDelAddress = $oOrder->getDelAddressInfo();
        }
        if (Registry::getSession()->getVariable('blshowshipaddress')) {
            $oDelAddress = $oUser->getSelectedAddress();
        }

        if ($oDelAddress instanceof Address) {
            $shippingAddress = $customer->getShippingAddress();
            /** @var string $oxcountryid */
            $oxcountryid = $oDelAddress->getFieldData('oxcountryid');
            /** @var string $deliveryCountryIso */
            $deliveryCountryIso = $oCountry->load($oxcountryid)
                ? $oDelAddress->getFieldData('oxisoalpha2')
                : '';

            /** @var string $oxcompany */
            $oxcompany = $oDelAddress->getFieldData('oxcompany');
            /** @var string $oxfname */
            $oxfname = $oDelAddress->getFieldData('oxfname');
            /** @var string $oxlname */
            $oxlname = $oDelAddress->getFieldData('oxlname');
            $shippingAddress->setName(!empty($oxcompany) ? $oxcompany : $oxfname . ' ' . $oxlname);
            $shippingAddress->setStreet(trim(
                $oDelAddress->getFieldData('oxstreet') .
                ' ' .
                $oDelAddress->getFieldData('oxstreetnr')
            ));

            /** @var string $oxzip */
            $oxzip = $oDelAddress->getFieldData('oxzip');
            $shippingAddress->setZip($oxzip);

            /** @var string $oxcity */
            $oxcity = $oDelAddress->getFieldData('oxstreet');
            $shippingAddress->setCity($oxcity);
            $shippingAddress->setCountry($deliveryCountryIso);
        }

        if ($companyType) {
            $companyInfo = new CompanyInfo();
            $customer->setCompanyInfo($companyInfo);
            $companyInfo->setRegistrationType(CompanyRegistrationTypes::REGISTRATION_TYPE_NOT_REGISTERED);
            $companyInfo->setFunction('OWNER');

            $companyInfo->setCommercialRegisterNumber(strval($oUser->getFieldData('oxustid')));
        }

        return $customer;
    }

    /**
     * @param string $unzerOrderId
     * @param BasketModel $basketModel
     * @return Basket
     */
    public function getUnzerBasket(string $unzerOrderId, BasketModel $basketModel): Basket
    {
        $basket = new Basket();
        $basket->setOrderId($unzerOrderId)
            ->setAmountTotalGross($basketModel->getPrice()->getBruttoPrice())
            ->setCurrencyCode($basketModel->getBasketCurrency()->name)
            ->setAmountTotalDiscount(0.0);

        // we add the "voucher" with this amount later. Prepayment will complain if it finds "voucher" AND the total
        // discount amount here (PayPal or creditcard will NOT!)

        // additional: Total Vat
        $amountTotalVat = 0;
        foreach ($basketModel->getProductVats(false) as $vatItem) {
            $amountTotalVat += $vatItem;
        }
        $basket->setAmountTotalVat($amountTotalVat);

        $shopBasketContents = $basketModel->getContents();

        $unzerBasketItems = $basket->getBasketItems();

        // Add Basket-Items
        /** @var \OxidEsales\Eshop\Application\Model\BasketItem $basketItem */
        foreach ($shopBasketContents as $basketItem) {
            $unzerBasketItem = new BasketItem();
            $unzerBasketItem->setTitle($basketItem->getTitle())
                ->setAmountNet($basketItem->getPrice()->getNettoPrice())
                ->setAmountPerUnit($basketItem->getUnitPrice()->getNettoPrice())
                ->setQuantity((int)$basketItem->getAmount())
                ->setType(BasketItemTypes::GOODS)
                ->setVat($basketItem->getPrice()->getVat())
                ->setAmountVat($basketItem->getPrice()->getVatValue())
                ->setAmountGross($basketItem->getPrice()->getBruttoPrice())
                ->setAmountPerUnitGross($basketItem->getUnitPrice()->getBruttoPrice());

            $unzerBasketItems[] = $unzerBasketItem;
        }

        // Add DeliveryCosts
        $deliveryCosts = $basketModel->getDeliveryCost();
        if ($deliveryCosts->getNettoPrice() > 0.) {
            $unzerBasketItem = new BasketItem();
            $unzerBasketItem->setTitle($this->translator->translate('SHIPPING_COST'))
                ->setAmountNet($deliveryCosts->getNettoPrice())
                ->setAmountPerUnit($deliveryCosts->getNettoPrice())
                ->setQuantity(1)
                ->setType(BasketItemTypes::SHIPMENT)
                ->setVat($deliveryCosts->getVat())
                ->setAmountVat($deliveryCosts->getVatValue())
                ->setAmountGross($deliveryCosts->getBruttoPrice())
                ->setAmountPerUnitGross($deliveryCosts->getBruttoPrice());

            $unzerBasketItems[] = $unzerBasketItem;
        }

        // Add Discounts
        $discounts = $basketModel->getTotalDiscount();
        if ($discounts->getNettoPrice() > 0.) {
            $unzerBasketItem = new BasketItem();
            $unzerBasketItem->setTitle($this->translator->translate('DISCOUNT'))
                ->setAmountNet($discounts->getNettoPrice())
                ->setAmountPerUnit($discounts->getNettoPrice())
                ->setQuantity(1)
                ->setType(BasketItemTypes::VOUCHER)
                ->setVat($discounts->getVat())
                ->setAmountVat($discounts->getVatValue())
                ->setAmountGross($discounts->getBruttoPrice())
                ->setAmountPerUnitGross($discounts->getBruttoPrice());

            $unzerBasketItems[] = $unzerBasketItem;
        }
        $basket->setBasketItems($unzerBasketItems);
        $basket->setTotalValueGross($basketModel->getPrice()->getBruttoPrice());

        return $basket;
    }

    /**
     * @param Charge $charge
     * @return string
     */
    public function getBankDataFromCharge(Charge $charge): string
    {
        $bankData = sprintf(
            $this->translator->translate('OSCUNZER_BANK_DETAILS_AMOUNT'),
            $this->translator->formatCurrency($charge->getAmount() ?: 0),
            $this->context->getActiveCurrencySign()
        );

        $bankData .= sprintf(
            $this->translator->translate('OSCUNZER_BANK_DETAILS_HOLDER'),
            $charge->getHolder() ?: ''
        );

        $bankData .= sprintf(
            $this->translator->translate('OSCUNZER_BANK_DETAILS_IBAN'),
            $charge->getIban() ?: ''
        );

        $bankData .= sprintf(
            $this->translator->translate('OSCUNZER_BANK_DETAILS_BIC'),
            $charge->getBic() ?: ''
        );

        $bankData .= sprintf(
            $this->translator->translate('OSCUNZER_BANK_DETAILS_DESCRIPTOR'),
            $charge->getDescriptor() ?: ''
        );

        return $bankData;
    }

    /**
     * @param string $paymentMethod
     * @return string
     */
    public function getPaymentProcedure(string $paymentMethod): string
    {
        if (in_array($paymentMethod, ['paypal', 'card', 'installment-secured', 'applepay'])) {
            return $this->moduleSettings->getPaymentProcedureSetting($paymentMethod);
        }

        return $this->moduleSettings::PAYMENT_CHARGE;
    }

    /**
     * @param bool $addPending
     * @return string
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function prepareOrderRedirectUrl(bool $addPending = false): string
    {
        $redirectUrl = $this->prepareRedirectUrl('order');

        if ($addPending) {
            $redirectUrl .= '&fnc=unzerExecuteAfterRedirect';
        }

        return $redirectUrl;
    }

    /**
     * @return string
     */
    public function preparePdfConfirmRedirectUrl(): string
    {
        $redirectUrl = $this->prepareRedirectUrl('unzer_installment');

        return $redirectUrl;
    }

    /**
     * @param string $destination
     * @return string
     */
    public function prepareRedirectUrl(string $destination = ''): string
    {
        return Registry::getConfig()->getSslShopUrl() . 'index.php?cl=' . str_replace('?', '&', $destination);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getUnzerPaymentIdFromRequest(): string
    {
        /** @var string $jsonPaymentData */
        $jsonPaymentData = $this->request->getRequestParameter('paymentData');
        /** @var array $paymentData */
        $paymentData = $jsonPaymentData ? json_decode($jsonPaymentData, true) : [];

        if (array_key_exists('id', $paymentData)) {
            return $paymentData['id'];
        }

        throw new Exception('oscunzer_WRONGPAYMENTID');
    }

    /**
     * @param AbstractTransactionType $charge
     */
    public function setSessionVars(AbstractTransactionType $charge): void
    {
        // You'll need to remember the shortId to show it on the success or failure page
        if ($charge->getShortId() !== null && $this->session->getVariable('ShortId') !== $charge->getShortId()) {
            $this->session->setVariable('ShortId', $charge->getShortId());
        }

        $this->session->setVariable('PaymentId', $charge->getPaymentId());

        if ($charge instanceof Authorization) {
            $this->session->setVariable('UzrPdfLink', $charge->getPDFLink());
        }

        /** @var \UnzerSDK\Resources\Payment $payment */
        $payment = $charge->getPayment();
        $paymentType = $payment->getPaymentType();

        if (!$paymentType) {
            return;
        }

        // TODO: $charge is not only class of Charge possible here. Investigate and fix.
        if ($charge instanceof Charge && ($paymentType instanceof Prepayment || $paymentType->isInvoiceType())) {
            $this->session->setVariable(
                'additionalPaymentInformation',
                $this->getBankDataFromCharge($charge)
            );
        }
    }

    /**
     * @param string $paymentMethod
     * @return Metadata
     * @throws \Exception
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getShopMetadata(string $paymentMethod): Metadata
    {
        $metadata = new Metadata();
        $metadata->setShopType("Oxid eShop " . (new Facts())->getEdition());
        $metadata->setShopVersion(ShopVersion::getVersion());
        $metadata->addMetadata('shopid', (string)Registry::getConfig()->getShopId());
        $metadata->addMetadata('paymentmethod', $paymentMethod);
        $metadata->addMetadata('paymentprocedure', $this->getPaymentProcedure($paymentMethod));
        $metadata->addMetadata('moduleversion', $this->moduleSettings->getModuleVersion());

        return $metadata;
    }

    /**
     * @return string
     */
    public function generateUnzerOrderId(): string
    {
        return 'o' . str_replace(['0.', ' '], '', microtime(false));
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function setIsAjaxPayment(bool $isAjaxPayment = false): void
    {
        $this->session->setVariable('UzrAjaxRedirect', $isAjaxPayment);
    }

    /**
     * @return bool
     */
    public function isAjaxPayment(): bool
    {
        return (bool)$this->session->getVariable('UzrAjaxRedirect');
    }
}
