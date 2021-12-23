<?php

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidEsales\Eshop\Application\Model\Payment as PaymentModel;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use OxidSolutionCatalysts\Unzer\Service\PaymentExtensionLoader;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use UnzerSDK\Exceptions\UnzerApiException;

class PaymentGateway extends PaymentGateway_parent
{
    /**
     * @inerhitDoc
     */
    public function executePayment($dAmount, &$oOrder): bool
    {
        $oPayment = oxNew(PaymentModel::class);
        if ($oPayment->load($oOrder->getFieldData('oxpaymenttype'))) {
            if ($oPayment->isUnzerPayment()) {
                $this->executeUnzerPayment($oPayment);
            }
        }

        return parent::executePayment($dAmount, $oOrder);
    }

    public function executeUnzerPayment(PaymentModel $paymentModel): bool
    {
        $paymentStatus = false;
        $container = ContainerFactory::getInstance()->getContainer();

        try {
            /** @var PaymentExtensionLoader $paymentLoader */
            $paymentLoader = $container->get(PaymentExtensionLoader::class);
            $paymentExtension = $paymentLoader->getPaymentExtension($paymentModel);
            $paymentExtension->execute();
            $paymentStatus = $paymentExtension->checkPaymentstatus();
        } catch (UnzerApiException $e) {
            /** @var Translator $translator */
            $translator = $container->get(Translator::class);
            UnzerHelper::redirectOnError(
                $paymentExtension::CONTROLLER_URL,
                $translator->translate((string)$e->getCode(), $e->getClientMessage())
            );
        } catch (\Exception $e) {
            UnzerHelper::redirectOnError($paymentExtension::CONTROLLER_URL, $e->getMessage());
        }

        return $paymentStatus;
    }
}
