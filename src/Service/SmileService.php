<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SmileService
{
    private LoggerInterface $logger;

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiUrl,
        private string $apiUsername,
        private string $apiPassword,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function verifyLicense(string $license,$birthdate): array
    {
        $formattedDate = $birthdate->format('d/m/Y'); 

        try {
            $response = $this->httpClient->request(
                'GET',
                $this->apiUrl,
                [
                    'headers' => [
                        'numLicenceFFA' => $license,
                        'dateNaissance' => $formattedDate,
                        'API_Username' => $this->apiUsername,
                        'API_Password' => $this->apiPassword,
                        'Accept' => '*/*',
                        'Connection' => 'keep-alive',
                        'Accept-Encoding' => 'gzip,deflate,br',
                        'User-Agent' => 'SymfonyHttpClient',
                    ]
                ]
            );
            $isValid = false;            
            $isExist = false;
            $endingDate = null;

            $dataSmile = json_decode($response->getContent(false), true);

            if (!is_array($dataSmile)) {
                throw new \RuntimeException('Réponse Smile invalide ou non décodable');
            }

            if (($dataSmile['Licence_Valide'] ?? '') === 'Oui') { 
                $isValid = true;
                $endingDate = $dataSmile['Date_Fin'] ?? $endingDate;
            }

            if (($dataSmile['Numero_Licence_Existe'] ?? '') === 'Oui') {  
                $isExist = true;
            }
            $this->logger->info('Résultat de Smile API', [
                'raw_response' => $dataSmile,
                'isValid' => $isValid,
                'isExist' => $isExist,
                'endingDate' => $endingDate,
            ]);
            return [
                'isValid' => $isValid,
                'endingDate' => $endingDate,
                'isExist' => $isExist,
            ];        
        } catch (\Throwable $e) {
            $this->logger->error('Erreur lors de la vérification Smile', [
                'message' => $e->getMessage(),
                'license' => $license,
                'birthdate' => $birthdate->format('Y-m-d'),
            ]);   
            
            return [
                'error' => $e->getMessage(),
                'isValid' => false,
                'endingDate' => null,
                'isExist' => false
            ];
        }
    }
}