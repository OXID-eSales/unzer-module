<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use UnzerSDK\Services\ValueService;

/**
 * We only use this for requests the unzer sdk currently does not cover
 */
class ApiClient
{
    private Client $client;

    private DebugHandler $logger;

    private ModuleSettings $moduleSettings;

    /**
     * @var string[]
     */
    private array $headers;

    private string $baseUrl = 'https://api.unzer.com/v1/';

    /**
     * @param ModuleSettings $moduleSettings
     * @param DebugHandler $logger
     */
    public function __construct(
        ModuleSettings $moduleSettings,
        DebugHandler $logger
    )
    {
        $this->client = new Client();
        $this->logger = $logger;
        $this->moduleSettings = $moduleSettings;

        $this->headers = [
            'Accept' => '*/*',
            'Authorization' => 'Basic ' . base64_encode($this->moduleSettings->getShopPrivateKey() . ':')
        ];
    }

    /**
     * @param string $certificateId
     * @return ResponseInterface
     * @throws GuzzleException|JsonException
     */
    public function requestApplePayPaymentCert(string $certificateId): ResponseInterface
    {
        return $this->request('keypair/applepay/certificates/' . $certificateId);
    }

    /**
     * @param string $keyId
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function requestApplePayPaymentKey(string $keyId): ResponseInterface
    {
        return $this->request('keypair/applepay/privatekeys/' . $keyId);
    }

    /**
     * @param string $key
     * @return ResponseInterface
     * @throws GuzzleException
     * @throws JsonException
     */
    public function uploadApplePayPaymentKey(string $key): ResponseInterface
    {
        return $this->request('keypair/applepay/privatekeys', 'POST', [
            'format' => 'PEM',
            'type' => 'private-key',
            'certificate' => $key,
        ]);
    }

    /**
     * @param string $certificate
     * @param string $privateKeyId (getting from upload of ApplePayPaymentKey)
     * @return ResponseInterface
     * @throws GuzzleException
     * @throws JsonException
     */
    public function uploadApplePayPaymentCertificate(string $certificate, string $privateKeyId): ResponseInterface
    {
        return $this->request('keypair/applepay/certificates', 'POST', [
            'format' => 'PEM',
            'type' => 'certificate',
            'private-key' => $privateKeyId,
            'certificate' => $certificate,
        ]);
    }

    /**
     * @param string $certificateId
     * @return ResponseInterface
     * @throws GuzzleException
     * @throws JsonException
     */
    public function activateApplePayPaymentCertificate(string $certificateId): ResponseInterface
    {
        return $this->request('keypair/applepay/certificates/' . $certificateId . '/activate', 'POST');
    }

    /**
     * @throws JsonException
     */
    private function request(
        string $url,
        string $method = 'GET',
        array $body = [],
        array $headers = []
    ): ResponseInterface {
        $response = null;
        $options = [];
        $options['headers'] = array_merge($this->headers, $headers);
        $payload = '';
        if ($body) {
            $options['headers']['Content-Type'] = 'application/json';
            $payload = json_encode($body, JSON_THROW_ON_ERROR);
            $options['body'] = $payload;
        }

        try {
            $response = $this->client->request($method, $this->baseUrl . $url, $options);
        } catch (GuzzleException $e) {
            if ($this->moduleSettings->isDebugMode()) {
                // mask auth string
                $authHeader = explode(' ', $options['headers']['Authorization']);
                $authHeader[1] = ValueService::maskValue($authHeader[1]);
                $options['headers']['Authorization'] = implode(' ', $authHeader);

                // log request
                $this->logger->log($method . ': ' . $url);
                $this->logger->log('Headers: ' . json_encode($options['headers'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
                if ($payload) {
                    $this->logger->log('Request: ' . $payload);
                }
                $this->logger->log('ErrorMessage: ' . $e->getMessage());
            }
        }

        return $response;
    }
}
