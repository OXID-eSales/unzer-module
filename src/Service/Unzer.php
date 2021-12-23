<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Application\Model\Basket as BasketModel;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\ShopVersion;
use OxidEsales\Facts\Facts;
use UnzerSDK\Resources\Basket;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\CustomerFactory;
use UnzerSDK\Resources\EmbeddedResources\BasketItem;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\TransactionTypes\Charge;

class Unzer
{
    /** @var Session */
    protected $session;

    /** @var Language */
    protected $language;

    /** @var Context */
    protected $context;

    /** @var ModuleSettings */
    protected $moduleSettings;

    public function __construct(
        Session $session,
        Language $language,
        Context $context,
        ModuleSettings $moduleSettings
    ) {
        $this->session = $session;
        $this->language = $language;
        $this->context = $context;
        $this->moduleSettings = $moduleSettings;
    }

    public function getSessionCustomerData(?Order $oOrder = null): Customer
    {
        $oUser = $this->session->getUser();

        $customer = CustomerFactory::createCustomer($oUser->getFieldData('oxfname'), $oUser->getFieldData('oxlname'));
        $customer->setBirthDate($oUser->getFieldData('oxbirthdate') != "0000-00-00" ? $oUser->getFieldData('oxbirthdate') : '');
        $customer->setCompany($oUser->getFieldData('oxcompany'));
        $customer->setSalutation($oUser->getFieldData('oxsal'));
        $customer->setEmail($oUser->getFieldData('oxusername'));
        $customer->setPhone($oUser->getFieldData('oxfon'));
        $customer->setMobile($oUser->getFieldData('oxmobfon'));

        $billingAddress = $customer->getBillingAddress();

        $oCountry = oxNew(Country::class);
        $billingCountryIso = $oCountry->load($oUser->getFieldData('oxcountryid')) ? $oCountry->getFieldData('oxisoalpha2') : '';

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

        if ($oOrder !== null) {
            $oDelAddress = $oOrder->getDelAddressInfo();
            $shippingAddress = $customer->getShippingAddress();
            $deliveryCountryIso = $oCountry->load($oDelAddress->getFieldData('oxcountryid')) ? $oDelAddress->getFieldData('oxisoalpha2') : '';

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

        return $customer;
    }

    public function getUnzerBasket(string $unzerOrderId, BasketModel $basketModel): Basket
    {
        $basket = new Basket(
            $unzerOrderId,
            $basketModel->getNettoSum(),
            $basketModel->getBasketCurrency()->name
        );

        $shopBasketContents = $basketModel->getContents();

        $unzerBasketItems = $basket->getBasketItems();
        /** @var \OxidEsales\Eshop\Application\Model\BasketItem $basketItem */
        foreach ($shopBasketContents as $basketItem) {
            $unzerBasketItems[] = new BasketItem(
                $basketItem->getTitle(),
                $basketItem->getPrice()->getNettoPrice(),
                $basketItem->getUnitPrice()->getNettoPrice(),
                (int)$basketItem->getAmount()
            );
        }

        $basket->setBasketItems($unzerBasketItems);

        return $basket;
    }

    public function getBankDataFromCharge(Charge $charge): string
    {
        $bankData = sprintf(
            $this->language->translateString('OSCUNZER_BANK_DETAILS_AMOUNT'),
            $this->language->formatCurrency($charge->getAmount()),
            $this->context->getActiveCurrencySign()
        );

        $bankData .= sprintf(
            $this->language->translateString('OSCUNZER_BANK_DETAILS_HOLDER'),
            $charge->getHolder()
        );

        $bankData .= sprintf(
            $this->language->translateString('OSCUNZER_BANK_DETAILS_IBAN'),
            $charge->getIban()
        );

        $bankData .= sprintf(
            $this->language->translateString('OSCUNZER_BANK_DETAILS_BIC'),
            $charge->getBic()
        );

        $bankData .= sprintf(
            $this->language->translateString('OSCUNZER_BANK_DETAILS_DESCRIPTOR'),
            $charge->getDescriptor()
        );

        return $bankData;
    }

    /**
     * @return string
     */
    public function getPaymentProcedure(string $paymentId): string
    {
        if (in_array($paymentId, ['oscunzer_paypal', 'oscunzer_card'])) {
            return $this->moduleSettings->getPaymentProcedureSetting($paymentId);
        }

        return $this->moduleSettings::PAYMENT_DIRECT;
    }

    /**
     * @TODO: fix this method and add tests
     */
    public function prepareRedirectUrl(string $destination, bool $addSessionId = false): string
    {
        $dstUrl = Registry::getConfig()->getShopCurrentUrl();
        $destination = str_replace('?', '&', $destination);
        $dstUrl .= 'cl=' . $destination;

        if ($addSessionId) {
            $dstUrl .= '&force_sid=' . $this->session->getId();
        }

        return $this->session->processUrl($dstUrl);
    }
}
