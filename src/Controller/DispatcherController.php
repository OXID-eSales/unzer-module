<?php

/**
 * This Software is the property of OXID eSales and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * @copyright 2003-2021 OXID eSales AG
 * @author    OXID Solution Catalysts
 * @link      https://www.oxid-esales.com
 */

namespace OxidSolutionCatalysts\Unzer\Controller;

use Exception;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use OxidSolutionCatalysts\Unzer\Service\PaymentExtensionLoader;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use UnzerSDK\Exceptions\UnzerApiException;

class DispatcherController extends FrontendController
{
    /**
     * @param string $paymentid
     * @return bool
     */
    public function executePayment(string $paymentid): bool
    {
        $paymentModel = oxNew(Payment::class);
        $paymentModel->load($paymentid);

        $container = ContainerFactory::getInstance()->getContainer();

        /** @var PaymentExtensionLoader $paymentLoader */
        $paymentLoader = $container->get(PaymentExtensionLoader::class);
        $paymentExtension = $paymentLoader->getPaymentExtension($paymentModel);

        try {
            $paymentExtension->execute();
        } catch (UnzerApiException $e) {
            /** @var Translator $translator */
            $translator = $container->get(Translator::class);
            UnzerHelper::redirectOnError(
                $paymentExtension::CONTROLLER_URL,
                $translator->translate((string)$e->getCode(), $e->getClientMessage())
            );
        } catch (Exception $e) {
            UnzerHelper::redirectOnError($paymentExtension::CONTROLLER_URL, $e->getMessage());
        }
        return $paymentExtension->checkPaymentstatus();
    }
}
