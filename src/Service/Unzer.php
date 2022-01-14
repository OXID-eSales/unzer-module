<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Application\Model\Basket as BasketModel;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\ShopVersion;
use OxidEsales\Facts\Facts;
use UnzerSDK\Resources\Basket;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\CustomerFactory;
use UnzerSDK\Resources\EmbeddedResources\BasketItem;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\TransactionTypes\AbstractTransactionType;
use UnzerSDK\Resources\TransactionTypes\Charge;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\EshopCommunity\Core\Field as FieldAlias;

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

    public function getUnzerCustomer(\OxidEsales\Eshop\Application\Model\User $oUser, ?Order $oOrder = null): Customer
    {
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

        if ($oOrder && $oDelAddress = $oOrder->getDelAddressInfo()) {
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

    public function getPaymentProcedure(string $paymentMethod): string
    {
        if (in_array($paymentMethod, ['paypal', 'card'])) {
            return $this->moduleSettings->getPaymentProcedureSetting($paymentMethod);
        }

        return $this->moduleSettings::PAYMENT_CHARGE;
    }

    public function prepareOrderRedirectUrl(bool $addPending = false): string
    {
        $redirectUrl = $this->prepareRedirectUrl('order');

        if ($addPending) {
            $redirectUrl .= '&fnc=unzerExecuteAfterRedirect';
        }

        return $redirectUrl;
    }

    public function prepareRedirectUrl(string $destination = ''): string
    {
        return Registry::getConfig()->getSslShopUrl() . 'index.php?cl=' . $destination;
    }

    public function getUnzerPaymentIdFromRequest(): string
    {
        $jsonPaymentData = $this->request->getRequestParameter('paymentData');
        $paymentData = $jsonPaymentData ? json_decode($jsonPaymentData, true) : [];

        if (array_key_exists('id', $paymentData)) {
            return $paymentData['id'];
        }

        throw new \Exception('oscunzer_WRONGPAYMENTID');
    }

    public function setSessionVars(AbstractTransactionType $charge): void
    {
        // You'll need to remember the shortId to show it on the success or failure page
        $this->session->setVariable('ShortId', $charge->getShortId());
        $this->session->setVariable('PaymentId', $charge->getPaymentId());

        $paymentType = $charge->getPayment()->getPaymentType();

        if (!$paymentType) {
            return;
        }

        // TODO: $charge is not only class of Charge possible here. Investigate and fix.
        if ($paymentType instanceof \UnzerSDK\Resources\PaymentTypes\Prepayment || $paymentType->isInvoiceType()) {
            $this->session->setVariable(
                'additionalPaymentInformation',
                $this->getBankDataFromCharge($charge)
            );
        }
    }

    public function getShopMetadata(string $paymentMethod): Metadata
    {
        $metadata = new Metadata();
        $metadata->setShopType("Oxid eShop " . (new Facts())->getEdition());
        $metadata->setShopVersion(ShopVersion::getVersion());
        $metadata->addMetadata('shopid', (string)Registry::getConfig()->getShopId());
        $metadata->addMetadata('paymentmethod', $paymentMethod);
        $metadata->addMetadata('paymentprocedure', $this->getPaymentProcedure($paymentMethod));

        return $metadata;
    }

    public function generateUnzerOrderId(): string
    {
        return 'o' . str_replace(['0.', ' '], '', microtime(false));
    }
}
