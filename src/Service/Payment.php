<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use Exception;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\Payment as PaymentModel;
use OxidEsales\Eshop\Core\Registry;
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

    /** @var UnzerSDKLoader */
    protected $unzerSDKLoader;

    public function __construct(
        Session $session,
        PaymentExtensionLoader $paymentExtensionLoader,
        Translator $translator,
        Unzer $unzerService,
        UnzerSDKLoader $unzerSDKLoader
    ) {
        $this->session = $session;
        $this->paymentExtensionLoader = $paymentExtensionLoader;
        $this->translator = $translator;
        $this->unzerService = $unzerService;
        $this->unzerSDKLoader = $unzerSDKLoader;
    }

    /**
     * @throws Redirect
     * @throws RedirectWithMessage
     */
    public function executeUnzerPayment(PaymentModel $paymentModel): bool
    {
        try {
            $paymentExtension = $this->paymentExtensionLoader->getPaymentExtension($paymentModel);
            $paymentExtension->execute();

            if (!$paymentId = $this->session->getVariable('PaymentId')) {
                throw new Exception("Something went wrong. Please try again later.");
            }

            $paymentStatus = $this->checkUnzerPaymentStatus($paymentId);
        } catch (Redirect $e) {
            throw $e;
        } catch (UnzerApiException $e) {
            $this->removeTemporaryOrder();

            throw new RedirectWithMessage(
                $this->unzerService->prepareRedirectUrl(
                    isset($paymentExtension) ? $paymentExtension::CONTROLLER_URL : UnzerPayment::CONTROLLER_URL
                ),
                $this->translator->translateCode((string)$e->getCode(), $e->getClientMessage())
            );
        } catch (Exception $e) {
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

    public function checkUnzerPaymentStatus($paymentId): bool
    {
        $result = false;

        // Redirect to success if the payment has been successfully completed.
        $unzerPayment = $this->getUnzerSDK()->fetchPayment($paymentId);
        if ($transaction = $unzerPayment->getInitialTransaction()) {
            if ($transaction->isSuccess()) {
                $result = true;
            } elseif ($transaction->isPending()) {
                $this->createPaymentStatusWebhook($paymentId);

                if ($redirectUrl = $transaction->getRedirectUrl()) {
                    throw new Redirect($redirectUrl);
                }
                $result = true;
            } elseif ($transaction->isError()) {
                throw new Exception($this->translator->translateCode(
                    $transaction->getMessage()->getCode(),
                    "Error in transaction for customer " . $transaction->getMessage()->getCustomer()
                ));
            }
        }

        return $result;
    }

    public function createPaymentStatusWebhook(string $unzerPaymentId): void
    {
        $webhookUrl = Registry::getConfig()->getShopUrl()
            . 'index.php?cl=unzer_dispatcher&fnc=updatePaymentTransStatus&paymentid='
            . $unzerPaymentId;

        $this->getUnzerSDK()->createWebhook($webhookUrl, 'payment');
    }

    protected function getUnzerSDK(): \UnzerSDK\Unzer
    {
        return $this->unzerSDKLoader->getUnzerSDK();
    }
}
