<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Session;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\CustomerFactory;

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

        $customer = CustomerFactory::createCustomer($oUser->oxuser__oxfname->value, $oUser->oxuser__oxlname->value);
        if ($oUser->oxuser__oxbirthdate->value != "0000-00-00") {
            $customer->setBirthDate($oUser->oxuser__oxbirthdate->value);
        }
        if ($oUser->oxuser__oxcompany->value) {
            $customer->setCompany($oUser->oxuser__oxcompany->value);
        }
        if ($oUser->oxuser__oxsal->value) {
            $customer->setSalutation($oUser->oxuser__oxsal->value);
        }
        if ($oUser->oxuser__oxusername->value) {
            $customer->setEmail($oUser->oxuser__oxusername->value);
        }
        if ($oUser->oxuser__oxfon->value) {
            $customer->setPhone($oUser->oxuser__oxfon->value);
        }

        $billingAddress = $customer->getBillingAddress();

        $oCountry = oxNew(Country::class);
        if ($oCountry->load($oUser->oxuser__oxcountryid->value)) {
            $billingAddress->setCountry($oCountry->oxcountry__oxisoalpha2->value);
        }
        if ($oUser->oxuser__oxcompany->value) {
            $billingAddress->setName($oUser->oxuser__oxcompany->value);
        } else {
            $billingAddress->setName($oUser->oxuser__oxfname->value . ' ' . $oUser->oxuser__oxlname->value);
        }

        if ($oUser->oxuser__oxcity->value) {
            $billingAddress->setCity(trim($oUser->oxuser__oxcity->value));
        }
        if ($oUser->oxuser__oxstreet->value) {
            $billingAddress->setStreet(
                $oUser->oxuser__oxstreet->value
                . ($oUser->oxuser__oxstreetnr->value !== '' ? ' ' . $oUser->oxuser__oxstreetnr->value : '')
            );
        }
        if ($oUser->oxuser__oxzip->value) {
            $billingAddress->setZip($oUser->oxuser__oxzip->value);
        }
        if ($oUser->oxuser__oxmobfon->value) {
            $customer->setMobile($oUser->oxuser__oxmobfon->value);
        }
        if ($oOrder !== null) {
            $oDelAddress = $oOrder->getDelAddressInfo();
            $shippingAddress = $customer->getShippingAddress();

            if ($oDelAddress->oxaddress__oxcompany->value) {
                $shippingAddress->setName($oDelAddress->oxaddress__oxcompany->value);
            } else {
                $shippingAddress->setName(
                    $oDelAddress->oxaddress__oxfname->value . ' ' . $oDelAddress->oxaddress__oxlname->value
                );
            }

            if ($oDelAddress->oxaddress__oxstreet->value) {
                $shippingAddress->setStreet(
                    $oDelAddress->oxaddress__oxstreet->value
                    . (
                    $oDelAddress->oxaddress__oxstreetnr->value !== ''
                        ? ' ' . $oDelAddress->oxaddress__oxstreetnr->value
                        : ''
                    )
                );
            }

            if ($oDelAddress->oxaddress__oxstreet->value) {
                $shippingAddress->setCity($oDelAddress->oxaddress__oxstreet->value);
            }

            if ($oDelAddress->oxaddress__oxzip->value) {
                $shippingAddress->setZip($oDelAddress->oxaddress__oxzip->value);
            }

            $oCountry = oxNew(Country::class);
            if ($oCountry->load($oDelAddress->oxaddress__oxcountryid->value)) {
                $oCountry->load($oDelAddress->oxaddress__oxcountryid->value);

                $billingAddress->setCountry($oCountry->oxcountry__oxisoalpha2->value);
            }
        }

        return $customer;
    }
}
