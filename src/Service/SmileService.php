<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class SmileService
{
    private $httpClient;
    private $apiBaseUrl;
    private $apiKey;

    public function __construct(
        HttpClientInterface $httpClient, 
        string $apiBaseUrl, 
        string $apiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiBaseUrl = $apiBaseUrl;
        $this->apiKey = $apiKey;
    }

    public function verifyLicense(string $license): array
    {
        $response = $this->httpClient->request(
            'GET',
            $this->apiBaseUrl . '?action=get_licencie_vol&num_lic=' . $license . $this->apiKey,
        );

        return $response->toArray();
    }
}