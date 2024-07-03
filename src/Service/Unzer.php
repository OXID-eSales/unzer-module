<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Service;

use Doctrine\DBAL\Driver\ResultStatement;
use Exception;
use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\Basket as BasketModel;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Counter;
use OxidEsales\Eshop\Core\Model\ListModel;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Price;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\ShopVersion;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\Facts\Facts;
use OxidSolutionCatalysts\Unzer\Exception\UnzerException;
use OxidSolutionCatalysts\Unzer\Model\Order as UnzerModelOrder;
use OxidSolutionCatalysts\Unzer\Model\UnzerPaymentData;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use UnzerSDK\Constants\CompanyRegistrationTypes;
use UnzerSDK\Constants\CompanyTypes;
use UnzerSDK\Constants\CustomerGroups;
use UnzerSDK\Constants\Salutations;
use UnzerSDK\Constants\ShippingTypes;
use UnzerSDK\Interfaces\UnzerParentInterface;
use UnzerSDK\Resources\Basket;
use UnzerSDK\Constants\BasketItemTypes;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\CustomerFactory;
use UnzerSDK\Resources\EmbeddedResources\BasketItem;
use UnzerSDK\Resources\EmbeddedResources\CompanyInfo;
use UnzerSDK\Resources\EmbeddedResources\RiskData;
use UnzerSDK\Resources\EmbeddedResources\Address as UnzerSDKAddress;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\PaymentTypes\Prepayment;
use UnzerSDK\Resources\TransactionTypes\AbstractTransactionType;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Resources\TransactionTypes\Charge;
use DateTime;

/**
 * TODO: Fix all the suppressed warnings
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class Unzer
{
    use ServiceContainer;

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
        $customer->setBirthDate($birthdate !== "0000-00-00" ? $birthdate : '');

        /** @var string $oxcompany */
        $oxcompany = $oUser->getFieldData('oxcompany');
        $customer->setCompany($oxcompany);

        /** @var null|string $oxsal */
        $oxsal = $oUser->getFieldData('oxsal');
        $oxsal = strtolower($oxsal ?? Salutations::UNKNOWN);
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

        /** @var string $customerId */
        $customerId = $oUser->getFieldData('oxcustnr');
        $customer->setCustomerId($customerId);

        $billingAddress = $customer->getBillingAddress();

        $oCountry = oxNew(Country::class);
        /** @var string $oxcountryid */
        $oxcountryid = $oUser->getFieldData('oxcountryid');
        /** @var string $billingCountryIso */
        $billingCountryIso = $oCountry->load($oxcountryid)
            ? $oCountry->getFieldData('oxisoalpha2')
            : '';

        $billingAddress->setName($oxfname . ' ' . $oxlname);

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
                ? $oCountry->getFieldData('oxisoalpha2')
                : '';
            $shippingAddress->setCountry($deliveryCountryIso);

            /** @var string $oxcompany */
            $oxcompany = $oDelAddress->getFieldData('oxcompany');
            /** @var string $oxfname */
            $oxfname = $oDelAddress->getFieldData('oxfname');
            /** @var string $oxlname */
            $oxlname = $oDelAddress->getFieldData('oxlname');
            $shippingAddress->setName($oxfname . ' ' . $oxlname);
            $shippingAddress->setStreet(trim(
                $oDelAddress->getFieldData('oxstreet') .
                ' ' .
                $oDelAddress->getFieldData('oxstreetnr')
            ));

            /** @var string $oxzip */
            $oxzip = $oDelAddress->getFieldData('oxzip');
            $shippingAddress->setZip($oxzip);

            /** @var string $oxcity */
            $oxcity = $oDelAddress->getFieldData('oxcity');
            $shippingAddress->setCity($oxcity);

            $billingAddress->setShippingType(ShippingTypes::DIFFERENT_ADDRESS);
            $shippingAddress->setShippingType(ShippingTypes::DIFFERENT_ADDRESS);
        } else {
            $billingAddress->setShippingType(ShippingTypes::EQUALS_BILLING);
            $customer->setShippingAddress($billingAddress);
        }

        if ($companyType) {
            $companyInfo = new CompanyInfo();
            $customer->setCompanyInfo($companyInfo);
            $companyTypes = [
                CompanyTypes::COMPANY,
                CompanyTypes::ASSOCIATION,
                CompanyTypes::AUTHORITY,
                CompanyTypes::SOLE,
                CompanyTypes::OTHER
            ];
            if (!in_array(strtolower($companyType), $companyTypes)) {
                throw new UnzerException('company type unknown');
            }
            $companyInfo->setCompanyType(strtolower($companyType));

            $companyInfo->setRegistrationType(CompanyRegistrationTypes::REGISTRATION_TYPE_NOT_REGISTERED);
            $companyInfo->setFunction('OWNER');

            /** @var string $sUstid */
            $sUstid = $oUser->getFieldData('oxustid');
            $companyInfo->setCommercialRegisterNumber($sUstid);
        }

        return $customer;
    }

    public function updateUnzerCustomer(Customer $unzerCustomer, Customer $oxidCustomer): bool
    {
        $hasChanged = false;
        // first, it must be the same customer...
        if ($unzerCustomer->getCustomerId() == $oxidCustomer->getCustomerId()) {
            if ($unzerCustomer->getFirstname() != $oxidCustomer->getFirstname()) {
                $hasChanged = true;
                $unzerCustomer->setFirstname($oxidCustomer->getFirstname() ?? '');
            }
            if ($unzerCustomer->getLastname() != $oxidCustomer->getLastname()) {
                $hasChanged = true;
                $unzerCustomer->setLastname($oxidCustomer->getLastname() ?? '');
            }
            if ($unzerCustomer->getSalutation() != $oxidCustomer->getSalutation()) {
                $hasChanged = true;
                $unzerCustomer->setSalutation($oxidCustomer->getSalutation());
            }
            if ($unzerCustomer->getBirthDate() != $oxidCustomer->getBirthDate()) {
                $hasChanged = true;
                $unzerCustomer->setBirthDate($oxidCustomer->getBirthDate() ?? '');
            }
            if ($unzerCustomer->getCompany() != $oxidCustomer->getCompany()) {
                $hasChanged = true;
                $unzerCustomer->setCompany($oxidCustomer->getCompany() ?? '');
            }
            if ($unzerCustomer->getEmail() != $oxidCustomer->getEmail()) {
                $hasChanged = true;
                $unzerCustomer->setEmail($oxidCustomer->getEmail() ?? '');
            }
            if ($unzerCustomer->getPhone() != $oxidCustomer->getPhone()) {
                $hasChanged = true;
                $unzerCustomer->setPhone($oxidCustomer->getPhone() ?? '');
            }
            if ($unzerCustomer->getMobile() != $oxidCustomer->getMobile()) {
                $hasChanged = true;
                $unzerCustomer->setMobile($oxidCustomer->getMobile() ?? '');
            }
            $hasChanged = $hasChanged || $this->updateUnzerAddress(
                $unzerCustomer->getBillingAddress(),
                $oxidCustomer->getBillingAddress()
            );
            $hasChanged = $hasChanged || $this->updateUnzerAddress(
                $unzerCustomer->getShippingAddress(),
                $oxidCustomer->getShippingAddress()
            );
        }

        return $hasChanged;
    }

    protected function updateUnzerAddress(UnzerSDKAddress $unzerAddress, UnzerSDKAddress $oxidAddress): bool
    {
        $hasChanged = false;

        if ($unzerAddress->getName() != $oxidAddress->getName()) {
            $hasChanged = true;
            $unzerAddress->setName($oxidAddress->getName() ?? '');
        }
        if ($unzerAddress->getStreet() != $oxidAddress->getStreet()) {
            $hasChanged = true;
            $unzerAddress->setStreet($oxidAddress->getStreet() ?? '');
        }
        if ($unzerAddress->getZip() != $oxidAddress->getZip()) {
            $hasChanged = true;
            $unzerAddress->setZip($oxidAddress->getZip() ?? '');
        }
        if ($unzerAddress->getCity() != $oxidAddress->getCity()) {
            $hasChanged = true;
            $unzerAddress->setCity($oxidAddress->getCity() ?? '');
        }
        if ($unzerAddress->getState() != $oxidAddress->getState()) {
            $hasChanged = true;
            $unzerAddress->setState($oxidAddress->getState() ?? '');
        }
        if ($unzerAddress->getCountry() != $oxidAddress->getCountry()) {
            $hasChanged = true;
            $unzerAddress->setCountry($oxidAddress->getCountry() ?? '');
        }
        if ($unzerAddress->getShippingType() != $oxidAddress->getShippingType()) {
            $hasChanged = true;
            $unzerAddress->setShippingType($oxidAddress->getShippingType());
        }

        return $hasChanged;
    }

    public function getUnzerBasket(string $unzerOrderId, BasketModel $basketModel): Basket
    {
        // v2 (BUT we need to keep the v1 methods for some reason...)
        $basket = new Basket();
        $basket->setOrderId($unzerOrderId)
            ->setAmountTotalGross($basketModel->getPrice()->getBruttoPrice())
            ->setAmountTotalDiscount(0.0)
            ->setCurrencyCode($basketModel->getBasketCurrency()->name);

        $priceForPayment = $basketModel->getPriceForPayment();
        $discountAmount = $basketModel->getTotalDiscount()->getPrice();

        $voucherAmount = 0.0;
        if (!is_null($basketModel->getVoucherDiscount())) {
            $voucherAmount = $basketModel->getVoucherDiscount()->getPrice();
        }

        $shopBasketContents = $basketModel->getContents();

        $unzerBasketItems = $basket->getBasketItems();

        // Add Basket-Items
        /** @var \OxidEsales\Eshop\Application\Model\BasketItem $basketItem */
        foreach ($shopBasketContents as $basketItem) {
            $unzerBasketItem = new BasketItem();
            $priceBrutto = $basketItem->getUnitPrice()->getBruttoPrice();
            $unzerBasketItem->setTitle($basketItem->getTitle())
                ->setQuantity((int)$basketItem->getAmount())
                ->setType(BasketItemTypes::GOODS)
                ->setAmountNet($priceBrutto)
                ->setAmountPerUnit($priceBrutto)
                ->setAmountVat($basketItem->getPrice()->getVatValue())
                ->setAmountGross($priceBrutto)
                ->setVat($basketItem->getPrice()->getVat())
                ->setAmountDiscountPerUnitGross(0.)
                ->setAmountPerUnitGross($priceBrutto);

            $unzerBasketItems[] = $unzerBasketItem;
        }

        // Add DeliveryCosts
        $deliveryCosts = $basketModel->getDeliveryCost();
        if ($deliveryCosts->getNettoPrice() > 0.) {
            $unzerBasketItem = new BasketItem();
            $unzerBasketItem->setTitle($this->translator->translate('SHIPPING_COST'))
                ->setQuantity(1)
                ->setType(BasketItemTypes::SHIPMENT)
                ->setAmountNet($deliveryCosts->getNettoPrice())
                ->setAmountPerUnit($deliveryCosts->getNettoPrice())
                ->setAmountVat($deliveryCosts->getVatValue())
                ->setAmountGross($deliveryCosts->getBruttoPrice())
                ->setVat($deliveryCosts->getVat())
                ->setAmountPerUnitGross($deliveryCosts->getBruttoPrice());

            $unzerBasketItems[] = $unzerBasketItem;
        }

        // Add Vouchers
        $totalVoucherAmount = $voucherAmount + $discountAmount;
        if ($totalVoucherAmount > 0.) {
            $unzerBasketItem = new BasketItem();
            $unzerBasketItem->setTitle($this->translator->translate('DISCOUNT'))
                ->setQuantity(1)
                ->setType(BasketItemTypes::VOUCHER)
                ->setAmountNet($totalVoucherAmount)
                ->setAmountPerUnit($totalVoucherAmount)
                ->setAmountGross($totalVoucherAmount)
                ->setVat(0)
                ->setAmountPerUnitGross(0.)
                ->setAmountDiscountPerUnitGross($totalVoucherAmount);

            $unzerBasketItems[] = $unzerBasketItem;
        } elseif ($totalVoucherAmount < 0.) {
            $totalVoucherAmount *= -1.;
            $unzerBasketItem = new BasketItem();
            $unzerBasketItem->setTitle($this->translator->translate('SURCHARGE'))
                ->setQuantity(1)
                ->setType(BasketItemTypes::GOODS)
                ->setAmountNet($totalVoucherAmount)
                ->setAmountPerUnit($totalVoucherAmount)
                ->setAmountGross($totalVoucherAmount)
                ->setVat(0)
                ->setAmountPerUnitGross($totalVoucherAmount)
                ->setAmountDiscountPerUnitGross(0.);

            $unzerBasketItems[] = $unzerBasketItem;
        }

        $basket->setBasketItems($unzerBasketItems);
        $basket->setTotalValueGross($priceForPayment);

        return $basket;
    }

    public function getUnzerRiskData(Customer $unzerCustomer, User $oUser): RiskData
    {
        $bPasswordIsEmpty = ($oUser->getFieldData('oxpassword') === '');

        $registrationLevel = '0';
        $registrationDate = gmdate('Ymd');
        if (!$bPasswordIsEmpty) { // registered user
            $registrationLevel = '1'; // 1 = registered user
            $oxregister = $oUser->getFieldData('oxregister');
            // shouldn't happen, but if it did, it would cause an error on unzer
            if ($oxregister == '0000-00-00 00:00:00') {
                $oxregister = gmdate('Y-m-d H:i:s');
            }
            /** @var string $oxregister */
            $dtRegister = new DateTime($oxregister);
            $registrationDate = $dtRegister->format('Ymd');
        }

        $orderedAmount = 0.;
        /** @var ListModel $orderList */
        $orderList = $oUser->getOrders();
        /** @var UnzerModelOrder $order */
        foreach ($orderList as $order) {
            $orderedAmount += $order->getTotalOrderSum();
        }

        $riskData = (new RiskData())
            ->setThreatMetrixId($this->getUnzerThreatMetrixIdFromSession())
            ->setConfirmedAmount($orderedAmount)
            ->setCustomerGroup(CustomerGroups::NEUTRAL) // todo: decide customer group (see doku)
            ->setConfirmedOrders($oUser->getOrderCount())
            ->setCustomerId($unzerCustomer->getCustomerId())
            ->setRegistrationLevel($registrationLevel)
            ->setRegistrationDate($registrationDate);

        return $riskData;
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
        if (in_array($paymentMethod, ['paypal', 'card', 'installment-secured', 'applepay', 'paylater-installment'])) {
            return $this->moduleSettings->getPaymentProcedureSetting($paymentMethod);
        }

        return $this->moduleSettings::PAYMENT_CHARGE;
    }

    /**
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

    public function preparePdfConfirmRedirectUrl(): string
    {
        $redirectUrl = $this->prepareRedirectUrl('unzer_installment');

        return $redirectUrl;
    }

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
        $paymentData = $this->getPaymentDataArrayFromRequest();

        if (array_key_exists('id', $paymentData)) {
            return $paymentData['id'];
        }

        throw new Exception('oscunzer_WRONGPAYMENTID');
    }

    public function setSessionVars(UnzerParentInterface $charge): void
    {
        /** @var \UnzerSDK\Resources\TransactionTypes\AbstractTransactionType $charge */
        // You'll need to remember the shortId to show it on the success or failure page
        if ($charge->getShortId() !== null && $this->session->getVariable('ShortId') !== $charge->getShortId()) {
            $this->session->setVariable('ShortId', $charge->getShortId());
        }

        $this->session->setVariable('UnzerPaymentId', $charge->getPaymentId());

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
        $metadata->addMetadata('pluginType', $this->moduleSettings->getGitHubName());
        $metadata->addMetadata('pluginVersion', $this->moduleSettings->getModuleVersion());

        return $metadata;
    }

    public function getUnzerPaymentDataFromRequest(): UnzerPaymentData
    {
        $paymentData = $this->getPaymentDataArrayFromRequest();

        return new UnzerPaymentData($paymentData);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function generateUnzerOrderId(): string
    {
        $config = Registry::getConfig();
        $session = Registry::getSession();
        $unzerOrderId = $session->getVariable('UnzerOrderId');
        $unzerOrderId = is_string($unzerOrderId) ? $unzerOrderId : '';
        if (!$unzerOrderId) {
            $separateNumbering = $config->getConfigParam('blSeparateNumbering');
            $counterIdent = $separateNumbering ? 'oxUnzerOrder_' . $config->getShopId() : 'oxUnzerOrder';
            $unzerOrderId = (string)oxNew(Counter::class)->getNext($counterIdent);
            $session->setVariable('UnzerOrderId', $unzerOrderId);
        }
        return $unzerOrderId;
    }

    public function resetUnzerOrderId(): void
    {
        Registry::getSession()->deleteVariable('UnzerOrderId');
    }

    public function generateUnzerThreatMetrixIdInSession(): string
    {
        $tmSessionID = Registry::getUtilsObject()->generateUID();
        Registry::getSession()->setVariable('unzerThreatMetrixSessionID', $tmSessionID);
        return $tmSessionID;
    }

    public function getUnzerThreatMetrixIdFromSession(): string
    {
        /** @var string $tmSessionID */
        $tmSessionID = Registry::getSession()->getVariable('unzerThreatMetrixSessionID');
        return $tmSessionID;
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function setIsAjaxPayment(bool $isAjaxPayment = false): void
    {
        $this->session->setVariable('UzrAjaxRedirect', $isAjaxPayment);
    }

    public function isAjaxPayment(): bool
    {
        return (bool)$this->session->getVariable('UzrAjaxRedirect');
    }

    public function ifImmediatePostAuthCollect(Payment $paymentService): bool
    {
        $paymentMethod = $this->getPaymentMethodFromOrder((string)$paymentService->getUnzerOrderId());
        $paymentProcedure = $this->getPaymentProcedure(str_replace('oscunzer_', '', $paymentMethod));
        return $paymentProcedure === ModuleSettings::PAYMENT_CHARGE;
    }

    private function getPaymentMethodFromOrder(string $oxUnzerOrderNr): string
    {
        /** @var QueryBuilderFactoryInterface $queryBuilderFactory */
        $queryBuilderFactory = $this->getServiceFromContainer(QueryBuilderFactoryInterface::class);

        $queryBuilder = $queryBuilderFactory->create();

        $query = $queryBuilder
            ->select('OXPAYMENTTYPE')
            ->from('oxorder')
            ->where("OXUNZERORDERNR = :oxunzerordernr");

        $parameters = [
            ':oxunzerordernr' => $oxUnzerOrderNr,
        ];

        $result = $query->setParameters($parameters)->execute();

        if ($result instanceof ResultStatement) {
            /** @var string $value */
            $value = $result->fetchColumn();
            if (empty($value)) {
                $value = '';
            }
            return $value;
        }

        return '';
    }

    private function getPaymentDataArrayFromRequest(): array
    {
        /** @var string $jsonPaymentData */
        $jsonPaymentData = $this->request->getRequestParameter('paymentData');
        return $jsonPaymentData ? (array) json_decode($jsonPaymentData, true) : [];
    }
}
