<?php

namespace App\Service;

use App\Entity\Enum\CRAList;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SmileService
{
//    LoggerInterface $logger;

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiUrlSmileDetails,
        private string $apiUsername,
        private string $apiPassword,
        private LoggerInterface $logger
    ) { }

 public function verifyLicense(string $license, ?\DateTimeInterface $birthdate): array
    {
        if (!$birthdate) {
            $this->logger->warning('Vérification Smile annulée : date de naissance absente', [
                'license' => $license,
            ]);

            return [
                'error' => 'Date de naissance non renseignée',
                'isValid' => false,
                'endingDate' => null,
            ];
        }
        if (!$license) {
            $this->logger->warning('Vérification Smile annulée : licence manquante', [
                'license' => $license,
            ]);

            return [
                'error' => 'License manquante',
                'isValid' => false,
                'endingDate' => null,
            ];
        }
        $formattedDate = $birthdate->format('d/m/Y');

        try {
            $response = $this->httpClient->request(
                'GET',
                $this->apiUrlSmileDetails,
                [
                    'headers' => [
                        'numLicenceFFA' => $license,
                        'dateNaissance' => $formattedDate,
                        'API_Username' => $this->apiUsername,
                        'API_Password' => $this->apiPassword,
                    ]
                ]
            );

            $dataSmile = $response->toArray(false);

            if (!is_array($dataSmile)) {
                throw new \RuntimeException('Réponse Smile invalide ou non décodable');
            }

            $this->logger->info('Résultat Smile reçu', [
                'license' => $license,
                'validity_licence' => $dataSmile['Licence_Valide'] ?? null,                
                'code_cra' => $dataSmile['code_cra'] ?? null,
            ]);

            $endingDate = null;

            if (!empty($dataSmile['Date_Fin'])) {
                $endingDate = \DateTimeImmutable::createFromFormat('d/m/Y', $dataSmile['Date_Fin']);
            }
            $lastnameSmile  = $dataSmile['nom'] ?? null;
            $firstnameSmile = $dataSmile['prenom'] ?? null;
            $emailSmile     = $dataSmile['email'] ?? null;
            $idClub         = $dataSmile['code_fna'] ?? null;
            $flyingclub     = $dataSmile['nom_aeroclub'] ?? null;
            $licenseValid   = $dataSmile['Licence_Valide'] ?? null;
            $statut = isset($dataSmile['Statut']) ? (int)$dataSmile['Statut'] : null;

            $endingDate = null;
            if (!empty($dataSmile['Date_Fin'])) {
                $endingDate = new \DateTimeImmutable($dataSmile['Date_Fin']);
            }

            if ($statut === -1) {
                return [
                    'isValid' => false,
                    'error' => $dataSmile['Erreur'] ?? 'Licence introuvable',
                    'endingDate' => $endingDate,
                ];
            }

            if ($licenseValid != 'Oui') {
                return [
                    'isValid' => false,
                    'error' => 'Licence '.$license.' non renouvelée',
                    'endingDate' => null,
                ];
            }

            $craEnum = null;

            if (!empty($dataSmile['code_cra'])) {
                if (preg_match('/\d+/', trim($dataSmile['code_cra']), $matches)) {
                    $codeNormalized = ltrim($matches[0], '0'); // "03 Bretagne" -> "3"
                    
                    $craEnum = CRAList::fromCode($codeNormalized);
                }
            }
            
            return [
                'isValid' => true,
                'endingDate' => $endingDate,
                'nom' => $lastnameSmile ?? null,
                'prenom' => $firstnameSmile ?? null,
                'email' => $emailSmile ?? null,
                'code_fna' => $idClub ?? null,
                'nom_aeroclub' => $flyingclub ?? null,
                'committee' => $craEnum,
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
            ];
        }
    }

}