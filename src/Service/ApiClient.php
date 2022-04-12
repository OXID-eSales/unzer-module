<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * We only use this for requests the unzer sdk currently does not cover
 */
class ApiClient
{
    /**
     * @var Client
     */
    private Client $client;

    /**
     * @var ModuleSettings
     */
    private ModuleSettings $moduleSettings;

    /**
     * @var string[]
     */
    private array $headers;

    private string $baseUrl = 'https://api.unzer.com/v1/';

    /**
     * @param ModuleSettings $moduleSettings
     */
    public function __construct(ModuleSettings $moduleSettings)
    {
        $this->client = new Client();
        $this->moduleSettings = $moduleSettings;

        $this->headers = [
            'Accept' => '*/*',
            'Authorization' => 'Basic ' . base64_encode($this->moduleSettings->getShopPrivateKey() . ':')
        ];
    }

    /**
     * @param string $certificateId
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function requestApplePayPaymentCert(string $certificateId): ResponseInterface
    {
        return $this->request('keypair/applepay/certificates/' . $certificateId);
    }

    /**
     * @param string $keyId
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function requestApplePayPaymentKey(string $keyId): ResponseInterface
    {
        return $this->request('keypair/applepay/privatekeys/' . $keyId);
    }

    /**
     * @param string $key
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public function activateApplePayPaymentCertificate(string $certificateId): ResponseInterface
    {
        return $this->request('keypair/applepay/certificates/' . $certificateId . '/activate', 'POST');
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    private function request(
        string $url,
        string $method = 'GET',
        array $body = [],
        array $headers = []
    ): ResponseInterface {
        $options['headers'] = array_merge($this->headers, $headers);
        if ($body) {
            $options['headers']['Content-Type'] = 'application/json';
            $options['body'] = json_encode($body, JSON_THROW_ON_ERROR);
        }

        return $this->client->request($method, $this->baseUrl . $url, $options);
    }
}
