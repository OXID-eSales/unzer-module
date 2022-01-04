<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\Payment as PaymentModel;
use OxidEsales\Eshop\Core\Session;
use OxidSolutionCatalysts\Unzer\Exception\Redirect;
use OxidSolutionCatalysts\Unzer\Exception\RedirectWithMessage;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\UnzerPayment;
use UnzerSDK\Exceptions\UnzerApiException;

class Payment
{
    /** @var Session */
    protected $session;

    /** @var PaymentExtensionLoader */
    protected $paymentExtensionLoader;

    /** @var Translator */
    protected $translator;

    /** @var Unzer */
    protected $unzerService;

    public function __construct(
        Session $session,
        PaymentExtensionLoader $paymentExtensionLoader,
        Translator $translator,
        Unzer $unzerService
    ) {
        $this->session = $session;
        $this->paymentExtensionLoader = $paymentExtensionLoader;
        $this->translator = $translator;
        $this->unzerService = $unzerService;
    }

    /**
     * @throws RedirectWithMessage
     */
    public function executeUnzerPayment(PaymentModel $paymentModel): bool
    {
        try {
            $paymentExtension = $this->paymentExtensionLoader->getPaymentExtension($paymentModel);
            $paymentExtension->execute();
            $paymentStatus = $paymentExtension->checkPaymentstatus();
        } catch (Redirect $e) {
            throw $e;
        } catch (UnzerApiException $e) {
            $this->removeTemporaryOrder();

            throw new RedirectWithMessage(
                $this->unzerService->prepareRedirectUrl(
                    isset($paymentExtension) ? $paymentExtension::CONTROLLER_URL : UnzerPayment::CONTROLLER_URL
                ),
                $this->translator->translate((string)$e->getCode(), $e->getClientMessage())
            );
        } catch (\Exception $e) {
            $this->removeTemporaryOrder();

            throw new RedirectWithMessage(
                $this->unzerService->prepareRedirectUrl(
                    isset($paymentExtension) ? $paymentExtension::CONTROLLER_URL : UnzerPayment::CONTROLLER_URL
                ),
                $e->getMessage()
            );
        }

        return $paymentStatus;
    }

    public function removeTemporaryOrder(): bool
    {
        $result = false;
        $sessionOrderId = $this->session->getVariable('sess_challenge');

        $orderModel = oxNew(Order::class);
        if ($orderModel->load($sessionOrderId)) {
            $result = $orderModel->delete();
        }

        return $result;
    }
}
