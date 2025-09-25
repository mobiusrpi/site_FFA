<?php  

namespace App\Service;

use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TestsRepository;
use App\Repository\CrewsRepository;
use App\Entity\TestResults;

class TrackAnalyzerImporter
{
    public function __construct(
        private EntityManagerInterface $em,
        private TestsRepository $testsRepository,
        private CrewsRepository $crewsRepository,
        private LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->testsRepository = $testsRepository;
        $this->crewsRepository = $crewsRepository;
        $this->logger = $logger;
    }

       /**
     * Traite l'import des résultats depuis les données JSON décodées.
     * 
     * @param array $data Données JSON décodées
     * @param bool $withFlightPlanning Indique si on traite le champ FlightPlanning
     * @param int|null $userId Optionnel, ID utilisateur qui fait l'import (pour logs, si besoin)
     * @return array Tableau associatif avec 'status', 'importedCrew', 'invalidCrew' et éventuellement 'error'
     */
    public function importResultsData(array $data, bool $withFlightPlanning = false, ?int $userId = null): array
    {
        if (empty($data['TestId']) || empty($data['Crews']) || !is_array($data['Crews'])) {
            $this->logger->error('Invalid JSON structure', ['data' => $data]);
            return ['error' => 'Invalid JSON structure'];
        }
        $test = $this->testsRepository->findOneBy(['code' => $data['TestId']]);
        if (!$test) {
            $this->logger->ERROR('Test code inconnu', ['code' => $data['TestId']]);

            return [
                'error' => 'Code de l\'épreuve inconnu',
                'details' => [
                    'field' => 'TestId',
                    'value' => $data['TestId'],
                    'message' => 'Le code "' . $data['TestId'] . '" n\'existe pas en base.).'
                ]
            ];
        }

        $competition = $test->getCompetition();
        $typeCompet = $competition?->getTypecompetition()?->getId();

        $invalidCrew = [];
        $importedCrew = [];

        // Récupérer les résultats existants pour ce test
        $existingResults = $this->em->getRepository(TestResults::class)->findBy(['test' => $test]);
        $existingByCrew = [];
        foreach ($existingResults as $res) {
            $existingByCrew[$res->getCrew()->getId()] = $res;
        }

        $incomingCrewIds = [];

        foreach ($data['Crews'] as $crewData) {
            $crewId = $crewData['CrewId'] ?? null;
            if (!$crewId) {
                $this->logger->warning('CrewId manquant dans le JSON', ['crewData' => $crewData]);
                continue;
            }
            $incomingCrewIds[] = $crewId;

            $crew = $this->crewsRepository->find($crewId);
            if (!$crew) {
                $this->logger->warning('Concurrent non trouvé', ['CrewId' => $crewId]);
                $invalidCrew[] = $crewId;
                continue;
            }

            $pilotName = $crew->getPilot()?->getLastname() . ' ' . $crew->getPilot()?->getFirstname();
            $navigatorName = $crew->getNavigator()?->getLastname() . ' ' . $crew->getNavigator()?->getFirstname();
            $crewLabel = trim($pilotName . ' / ' . $navigatorName);
            if (!empty($crewLabel) && $crewLabel !== '/') {
                $importedCrew[] = $crewLabel;
            }

            // Update si existant, create sinon
            $testResult = $existingByCrew[$crewId] ?? new TestResults();
            if (!isset($existingByCrew[$crewId])) {
                $testResult->setTest($test);
                $testResult->setCrew($crew);
                $this->em->persist($testResult);
            }

            // Remplir les champs depuis le JSON
            $testResult->setNavigation($crewData['Navigation'] ?? 0);
            if ($typeCompet === 1 || $typeCompet === 2) {
                $testResult->setObservation($crewData['Observation'] ?? 0);
            }
            if ($typeCompet === 2 && $withFlightPlanning) {
                $testResult->setFlightPlanning($crewData['FlightPlanning'] ?? 0);
            } else {
                $testResult->setLanding($crewData['Landing'] ?? 0);
            }
            $testResult->setCategory($crew->getCategory()?->value ?? null);
            $testResult->setStatus($crewData['Status'] ?? false);
        }

        // Supprimer les résultats pour les crews non présents dans le JSON
        if (count($incomingCrewIds) > 1) {
            foreach ($existingResults as $res) {
                if (!in_array($res->getCrew()->getId(), $incomingCrewIds)) {
                    $this->em->remove($res);
                }
            }
        }

        $this->em->flush();

        return [
            'status' => 'ok',
            'importedCrew' => $importedCrew,
            'invalidCrew' => $invalidCrew,
        ];
    }
}