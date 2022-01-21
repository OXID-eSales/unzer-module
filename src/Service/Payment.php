<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use Exception;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\Payment as PaymentModel;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidSolutionCatalysts\Unzer\Exception\Redirect;
use OxidSolutionCatalysts\Unzer\Exception\RedirectWithMessage;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\TransactionTypes\Authorization;

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

    protected $redirectUrl = null;

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
            $paymentExtension->execute(
                $this->session->getUser(),
                $this->session->getBasket()
            );

            $paymentStatus = ($this->getUnzerPaymentStatus() != "ERROR");

            if ($this->redirectUrl) {
                throw new Redirect($this->redirectUrl);
            }
        } catch (Redirect $e) {
            throw $e;
        } catch (UnzerApiException $e) {
            $this->removeTemporaryOrder();

            throw new RedirectWithMessage(
                $this->unzerService->prepareOrderRedirectUrl(
                    isset($paymentExtension) ? $paymentExtension->redirectUrlNeedPending() : false
                ),
                $this->translator->translateCode((string)$e->getCode(), $e->getClientMessage())
            );
        } catch (Exception $e) {
            $this->removeTemporaryOrder();

            throw new RedirectWithMessage(
                $this->unzerService->prepareOrderRedirectUrl(
                    isset($paymentExtension) ? $paymentExtension->redirectUrlNeedPending() : false
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

    /**
     * @return string
     * @throws UnzerApiException
     */
    public function getUnzerPaymentStatus(): string
    {
        $result = "ERROR";

        /** @var \UnzerSDK\Resources\Payment $sessionUnzerPayment */
        $sessionUnzerPayment = $this->getSessionUnzerPayment();
        $transaction = $sessionUnzerPayment->getInitialTransaction();

        if ($sessionUnzerPayment->isCompleted()) {
            $result = "OK";
        } elseif ($sessionUnzerPayment->isPending() && $transaction) {
            if ($transaction->isSuccess()) {
                if ($transaction instanceof Authorization) {
                    $result = "OK";
                }
            } elseif ($transaction->isPending()) {
                $result = "NOT_FINISHED";

                $this->createPaymentStatusWebhook($sessionUnzerPayment->getId());

                $this->redirectUrl = $transaction->getRedirectUrl();
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

    /**
     * @throws UnzerApiException
     */
    public function getSessionUnzerPayment(): ?\UnzerSDK\Resources\Payment
    {
        if ($paymentId = $this->session->getVariable('PaymentId')) {
            return $this->getUnzerSDK()->fetchPayment($paymentId);
        }

        return null;
    }
}
