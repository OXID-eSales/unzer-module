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
                $container = ContainerFactory::getInstance()->getContainer();
                /** @var \OxidSolutionCatalysts\Unzer\Service\Payment $paymentService */
                $paymentService = $container->get(\OxidSolutionCatalysts\Unzer\Service\Payment::class);
                $paymentService->executeUnzerPayment($oPayment);
            }
        }

        return parent::executePayment($dAmount, $oOrder);
    }
}
