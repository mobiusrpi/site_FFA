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
    public function importResults(array $data, bool $withFlightPlanning = false, ?int $userId = null): array
    {
        if (empty($data['TestId']) || empty($data['Crews']) || !is_array($data['Crews'])) {
            $this->logger->error('Invalid JSON structure', ['data' => $data]);
            return ['error' => 'Invalid JSON structure'];
        }

        $test = $this->testsRepository->findOneBy(['code' => $data['TestId']]);
        if (!$test) {
            return ['error' => 'Code de l\'épreuve inconnu : ' . $data['TestId']];
        }

        $competition = $test->getCompetition();
        $typeCompet = $competition?->getTypecompetition()?->getId();

        $invalidCrew = [];
        $importedCrew = [];

        foreach ($data['Crews'] as $crewData) {
            if (empty($crewData['CrewId'])) {
                $this->logger->warning('Id du concurrent non trouvé', ['CrewId' => $crewData['CrewId'] ?? null]);
                continue;
            }

            $crew = $this->crewsRepository->find($crewData['CrewId']);
            if (!$crew) {
                $this->logger->warning('Concurrent non trouvé', ['CrewId' => $crewData['CrewId']]);
                $invalidCrew[] = $crewData['CrewId'];
                continue;
            }

            $pilot = $crew->getPilot();
            $navigator = $crew->getNavigator();
            $pilotName = $pilot ? $pilot->getLastname() . ' ' . $pilot->getFirstname() : '';
            $navigatorName = $navigator ? $navigator->getLastname() . ' ' . $navigator->getFirstname() : '';
            $crewLabel = trim($pilotName . ' / ' . $navigatorName);
            if (!empty($crewLabel) && $crewLabel !== '/') {
                $importedCrew[] = $crewLabel;
            }

            $existingResults = $this->em->getRepository(TestResults::class)->findBy([
                'test' => $test,
                'crew' => $crew->getId(),
            ]);
            foreach ($existingResults as $result) {
                $this->em->remove($result);
            }
            if (!empty($existingResults)) {
                $this->em->flush();
            }

            $testResult = new TestResults();
            $testResult->setTest($test);
            $testResult->setCrew($crew);
            $testResult->setCategory($crew->getCategory()->value);
            $testResult->setNavigation($crewData['Navigation'] ?? null);

            if ($typeCompet === 1) {
                $testResult->setObservation($crewData['Observation'] ?? null);
            }
            
            if ($typeCompet === 2 && $withFlightPlanning) {
                $testResult->setFlightPlanning($crewData['FlightPlanning'] ?? null);
            } else {
                $testResult->setLanding($crewData['Landing'] ?? null);
            }
            
            $testResult->setStatus($crewData['Status'] ?? false);

            $this->em->persist($testResult);
        }

        $this->em->flush();

        return [
            'status' => 'ok',
            'importedCrew' => $importedCrew,
            'invalidCrew' => $invalidCrew,
        ];
    }
}