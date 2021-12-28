<?php

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidEsales\Eshop\Application\Model\Payment as PaymentModel;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
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
            $this->removeTemporaryOrder();

            /** @var Translator $translator */
            $translator = $container->get(Translator::class);

            /** @var \OxidSolutionCatalysts\Unzer\Service\Unzer $unzerService */
            $unzerService = $container->get(\OxidSolutionCatalysts\Unzer\Service\Unzer::class);

            throw new \OxidSolutionCatalysts\Unzer\Exception\RedirectWithMessage(
                $unzerService->prepareRedirectUrl($paymentExtension::CONTROLLER_URL),
                $translator->translate((string)$e->getCode(), $e->getClientMessage())
            );
        } catch (\Exception $e) {
            $this->removeTemporaryOrder();

            /** @var \OxidSolutionCatalysts\Unzer\Service\Unzer $unzerService */
            $unzerService = $container->get(\OxidSolutionCatalysts\Unzer\Service\Unzer::class);

            throw new \OxidSolutionCatalysts\Unzer\Exception\RedirectWithMessage(
                $unzerService->prepareRedirectUrl($paymentExtension::CONTROLLER_URL),
                $e->getMessage()
            );
        }

        return $paymentStatus;
    }

    public function removeTemporaryOrder(): void
    {
        // redirect to payment-selection page:
        $oSession = Registry::getSession();

        //Remove temporary order
        $oOrder = oxNew(Order::class);
        if ($oOrder->load($oSession->getVariable('sess_challenge'))) {
            $oOrder->delete();
        }
    }
}
