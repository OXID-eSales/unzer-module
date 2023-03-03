<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Service;

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
use UnzerSDK\Resources\Basket;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\CustomerFactory;
use UnzerSDK\Resources\EmbeddedResources\BasketItem;
use UnzerSDK\Resources\EmbeddedResources\CompanyInfo;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\PaymentTypes\Prepayment;
use UnzerSDK\Resources\TransactionTypes\AbstractTransactionType;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Resources\TransactionTypes\Charge;

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
     * @return Customer
     */
    public function getUnzerCustomer(
        User $oUser,
        ?Order $oOrder = null,
        string $commercialSector = '',
        string $commercialRegisterNumber = ''
    ): Customer {
        $customer = CustomerFactory::createCustomer(
            $oUser->getFieldData('oxfname'),
            $oUser->getFieldData('oxlname')
        );

        if ($birthdate = Registry::getRequest()->getRequestParameter('birthdate')) {
            $oUser->oxuser__oxbirthdate = new Field($birthdate, FieldAlias::T_RAW);
        }

        $customer->setBirthDate(
            $oUser->getFieldData('oxbirthdate') != "0000-00-00"
                ? $oUser->getFieldData('oxbirthdate')
                : ''
        );

        $customer->setCompany($oUser->getFieldData('oxcompany'));
        $customer->setSalutation($oUser->getFieldData('oxsal'));
        $customer->setEmail($oUser->getFieldData('oxusername'));
        $customer->setPhone($oUser->getFieldData('oxfon'));
        $customer->setMobile($oUser->getFieldData('oxmobfon'));

        $billingAddress = $customer->getBillingAddress();

        $oCountry = oxNew(Country::class);
        $billingCountryIso = $oCountry->load($oUser->getFieldData('oxcountryid'))
            ? $oCountry->getFieldData('oxisoalpha2')
            : '';

        $billingAddress->setName(
            $oUser->getFieldData('oxcompany') ??
            $oUser->getFieldData('oxfname') . ' ' . $oUser->getFieldData('oxlname')
        );
        $billingAddress->setStreet(trim(
            $oUser->getFieldData('oxstreet') .
            ' ' .
            $oUser->getFieldData('oxstreetnr')
        ));

        $billingAddress->setZip($oUser->getFieldData('oxzip'));
        $billingAddress->setCity(trim($oUser->getFieldData('oxcity')));
        $billingAddress->setCountry($billingCountryIso);

        $oDelAddress = null;
        if ($oOrder) {
            $oDelAddress = $oOrder->getDelAddressInfo();
        }
        if (Registry::getSession()->getVariable('blshowshipaddress')) {
            $oDelAddress = $oUser->getSelectedAddress();
        }

        if ($oDelAddress) {
            $shippingAddress = $customer->getShippingAddress();
            $deliveryCountryIso = $oCountry->load($oDelAddress->getFieldData('oxcountryid'))
                ? $oDelAddress->getFieldData('oxisoalpha2')
                : '';

            $shippingAddress->setName(
                $oDelAddress->getFieldData('oxcompany') ??
                $oDelAddress->getFieldData('oxfname') . ' ' . $oDelAddress->getFieldData('oxlname')
            );
            $shippingAddress->setStreet(trim(
                $oDelAddress->getFieldData('oxstreet') .
                ' ' .
                $oDelAddress->getFieldData('oxstreetnr')
            ));

            $shippingAddress->setZip($oDelAddress->getFieldData('oxzip'));
            $shippingAddress->setCity($oDelAddress->getFieldData('oxstreet'));
            $shippingAddress->setCountry($deliveryCountryIso);
        }

        if ($commercialRegisterNumber || $commercialSector) {
            $companyInfo = new CompanyInfo();
            $companyInfo->setCommercialRegisterNumber($commercialRegisterNumber);
            $companyInfo->setCommercialSector($commercialSector);
            $companyInfo->setRegistrationType($commercialRegisterNumber ? 'registered' : 'not_registered');
            $companyInfo->setFunction(!$commercialRegisterNumber ? 'OWNER' : '');
            $customer->setCompanyInfo($companyInfo);
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
        $basket = new Basket(
            $unzerOrderId,
            $basketModel->getPrice()->getBruttoPrice(),
            $basketModel->getBasketCurrency()->name
        );

        // additional: Total Discounts
        $basket->setAmountTotalDiscount($basketModel->getTotalDiscount()->getBruttoPrice());

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
            $unzerBasketItem = new BasketItem(
                $basketItem->getTitle(),
                $basketItem->getPrice()->getNettoPrice(),
                $basketItem->getUnitPrice()->getNettoPrice(),
                (int)$basketItem->getAmount()
            );
            $unzerBasketItem->setType('goods');
            $unzerBasketItem->setVat($basketItem->getPrice()->getVat());
            $unzerBasketItem->setAmountVat($basketItem->getPrice()->getVatValue());
            $unzerBasketItem->setAmountGross($basketItem->getPrice()->getBruttoPrice());

            $unzerBasketItems[] = $unzerBasketItem;
        }

        // Add DeliveryCosts
        $deliveryCosts = $basketModel->getDeliveryCost();
        $unzerBasketItem = new BasketItem(
            $this->translator->translate('SHIPPING_COST'),
            $deliveryCosts->getNettoPrice(),
            $deliveryCosts->getNettoPrice(),
            1
        );
        $unzerBasketItem->setType('shipment');
        $unzerBasketItem->setVat($deliveryCosts->getVat());
        $unzerBasketItem->setAmountVat($deliveryCosts->getVatValue());
        $unzerBasketItem->setAmountGross($deliveryCosts->getBruttoPrice());

        $unzerBasketItems[] = $unzerBasketItem;

        // Add Discounts
        $discounts = $basketModel->getTotalDiscount();
        $unzerBasketItem = new BasketItem(
            $this->translator->translate('DISCOUNT'),
            $discounts->getNettoPrice(),
            $discounts->getNettoPrice(),
            1
        );
        $unzerBasketItem->setType('voucher');
        $unzerBasketItem->setVat($discounts->getVat());
        $unzerBasketItem->setAmountVat($discounts->getVatValue());
        $unzerBasketItem->setAmountGross($discounts->getBruttoPrice());

        $unzerBasketItems[] = $unzerBasketItem;

        $basket->setBasketItems($unzerBasketItems);

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
        $jsonPaymentData = $this->request->getRequestParameter('paymentData');
        $paymentData = $jsonPaymentData ? json_decode($jsonPaymentData, true) : [];

        if (array_key_exists('id', $paymentData)) {
            return $paymentData['id'];
        }

        throw new \Exception('oscunzer_WRONGPAYMENTID');
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

        $paymentType = $charge->getPayment()->getPaymentType();

        if (!$paymentType) {
            return;
        }

        // TODO: $charge is not only class of Charge possible here. Investigate and fix.
        if ($paymentType instanceof Prepayment || $paymentType->isInvoiceType()) {
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
     */
    public function setIsAjaxPayment($isAjaxPayment = false): void
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
