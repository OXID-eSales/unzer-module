<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Application\Model\Basket as BasketModel;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Session;
use UnzerSDK\Resources\Basket;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\CustomerFactory;
use UnzerSDK\Resources\EmbeddedResources\BasketItem;

class Unzer
{
    protected $session;

    public function __construct(
        Session $session
    ) {
        $this->session = $session;
    }

    public function getSessionCustomerData(?Order $oOrder = null): Customer
    {
        $oUser = $this->session->getUser();

        $customer = CustomerFactory::createCustomer($oUser->getFieldData('oxfname'), $oUser->getFieldData('oxlname'));
        $customer->setBirthDate($oUser->getFieldData('oxbirthdate') != "0000-00-00" ?? '');
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

    public function getUnzerBasket($unzerOrderId, BasketModel $oBasket): Basket
    {
        $basket = new Basket(
            $unzerOrderId,
            $oBasket->getNettoSum(),
            $oBasket->getBasketCurrency()->name
        );

        $basketContents = $oBasket->getContents();

        $aBasketItems = $basket->getBasketItems();
        /**
         * @var string $sBasketItemKey
         * @var \OxidEsales\Eshop\Application\Model\BasketItem $oBasketItem
         */
        foreach ($basketContents as $oBasketItem) {
            $aBasketItems[] = new BasketItem(
                $oBasketItem->getTitle(),
                $oBasketItem->getPrice()->getNettoPrice(),
                $oBasketItem->getUnitPrice()->getNettoPrice(),
                (int)$oBasketItem->getAmount()
            );
        }

        $basket->setBasketItems($aBasketItems);

        return $basket;
    }
}
