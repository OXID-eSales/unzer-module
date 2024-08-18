<?php

namespace OxidSolutionCatalysts\Unzer\Service\Payment;

use OxidEsales\Eshop\Core\Session;
use OxidSolutionCatalysts\Unzer\Service\Payment;

/**
 * the Payment service has an intransparent dependency, that it needs the session variables UnzerPaymentId and
 * sess_challenge defined, this service encapsulates this by defining the needed parameters
 */
class GetPaymentType
{
    /** @var Payment $paymentService */
    private $paymentService;
    /** @var Session $session */
    private $session;

    public function __construct(Payment $paymentService, Session $session)
    {
        $this->paymentService = $paymentService;
        $this->session = $session;
    }

    public function getUnzerPaymentStatus(string $unzerPaymentId, string $orderId): string
    {
        $this->session->setVariable('UnzerPaymentId', $unzerPaymentId);
        $this->session->setVariable('sess_challenge', $orderId);
        return $this->paymentService->getUnzerPaymentStatus();
    }
}
