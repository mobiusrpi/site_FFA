<?php 
// src/Controller/TrackAnalyzerController.php
namespace App\Controller;

use App\Entity\TestResults;
use Psr\Log\LoggerInterface;
use App\Repository\CrewsRepository;
use App\Repository\TestsRepository;
use Psr\Cache\CacheItemPoolInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrackanalyzerController extends AbstractController
{
    #[Route('/3rdparty/trackanalyzer/import-live-data', name: 'import_trackanalyzer_live_data', methods: ['POST'])]
    public function importLiveData(
        Request $request,
        CacheItemPoolInterface $cache,
        EntityManagerInterface $em,
        TestsRepository $repositoryTest,
        CrewsRepository $repositoryCrew,
        LoggerInterface $logger
    ): JsonResponse {
        $authHeader = $request->headers->get('Authorization');
        $rawJson = $request->getContent();

        $logger->info('TrackAnalyzer import called', [
            'Authorization' => $authHeader,
            'Raw JSON' => $rawJson,
        ]);
        $data = json_decode($rawJson, true);
        if (!$data) {
            $logger->error('Invalid JSON received', ['raw' => $rawJson]);
        }
        $logger->debug('Parsed JSON:', $data);

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return new JsonResponse(['error' => 'Missing or malformed Authorization header'], 401);
        }

        $token = trim(substr($authHeader, 7));
        $item = $cache->getItem('trackanalyzer_token_' . $token);
        if (!$item->isHit()) {
            return new JsonResponse(['error' => 'Invalid or expired token'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data || empty($data['testId']) || empty($data['Crews']) || !is_array($data['Crews'])) {
            return new JsonResponse(['error' => 'Invalid JSON structure'], 400);
        }

        $test = $repositoryTest->findOneBy(['code' => $data['testId']]); // adjust if you use a different field
        if (!$test) {
            return new JsonResponse(['error' => 'Code de l\'épreuve inconnu'], 404);
        }

        $results = [];

        foreach ($data['Crews'] as $crewData) {
            if (empty($crewData['CrewId'])) {
                continue;
            }

            $crew = $repositoryCrew->find($crewData['CrewId']);
            if (!$crew) {
                $logger->warning('Comcurrents non trouvé', ['CrewId' => $crewData['CrewId']]);
                continue;
            }

            // 🔍 Try to find existing TestResults
            $testResult = $em->getRepository(TestResults::class)->findOneBy([
                'test' => $test,
                'crew' => $crew,
            ]);
            if (!$testResult) {
                $testResult = new TestResults();
                $testResult->setTest($test);
            }                
            $testResult->setCrew($crew);
            $testResult->setNavigation($crewData['Nav'] ?? null);
            $testResult->setLanding($data['Att'] ?? null);            
            $testResult->setObservation($data['Obs'] ?? null);

            $em->persist($testResult);
            $results[] = $testResult;
        }

        $em->flush();

        return new JsonResponse([
            'status' => 'ok',
            'imported' => count($results),
        ]);
    }

    #[Route('/3rdparty/trackanalyzer/import-results-data', name: 'import_trackanalyzer_results_data', methods: ['POST'])]
    public function importResultsData(
        Request $request,
        EntityManagerInterface $em,
        TestsRepository $repositoryTest,
        CrewsRepository $repositoryCrew,
        LoggerInterface $logger
    ): JsonResponse {
        $authHeader = $request->headers->get('Authorization');
        $rawJson = $request->getContent();

        $logger->info('TrackAnalyzer import called', [
            'Authorization' => $authHeader,
        ]);

        $data = json_decode($rawJson, true);
        if (!$data) {
            $logger->error('Invalid JSON received', ['raw' => $rawJson]);
        }
        $logger->debug('Parsed JSON:', $data);

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return new JsonResponse(['error' => 'Missing or malformed Authorization header'], 401);
        }
        
        /** @var \App\Entity\Users $user */
        $user = $this->getUser(); 
        $logger->info('Import called by user', [
            'email' => $user?->getEmail() ?? 'unknown',
        ]);

        $data = json_decode($request->getContent(), true);
        if (!$data || empty($data['testId']) || empty($data['Crews']) || !is_array($data['Crews'])) {
            return new JsonResponse(['error' => 'Invalid JSON structure'], 400);
        }

        $test = $repositoryTest->findOneBy(['code' => $data['testId']]); 
        if (!$test) {
            return new JsonResponse(['error' => 'Code de l\'épreuve inconnu : ' . $data['testId']], 404);
            exit();
        }

        $existingResults = $em->getRepository(TestResults::class)->findBy(['test' => $test]);
        if (!empty($existingResults)) {
            foreach ($existingResults as $result) {
                $em->remove($result);
            }
            $em->flush();
        }
        $results = [];

        foreach ($data['Crews'] as $crewData) {
            if ($crewData['Status'] == true) {
                if (empty($crewData['CrewId'])) {
                    continue;
                }

                $crew = $repositoryCrew->find($crewData['CrewId']);
                if (!$crew) {
                    $logger->warning('Comcurrents non trouvé', ['CrewId' => $crewData['CrewId']]);
                    continue;
                }

                $testResult = new TestResults();
                $testResult->setTest($test);                
                $testResult->setCrew($crew);

            }
            else{
                $testResult = new TestResults();
                $testResult->setTest($test);                 
            }                  
            $testResult->setCategory($crewData['Category'] ?? null);            
            $testResult->setNavigation($crewData['Nav'] ?? null);
            $testResult->setLanding($crewData['Att'] ?? null);            
            $testResult->setObservation($crewData['Obs'] ?? null);
            $testResult->setFlightPlanning($crewData['FlightPlanning'] ?? null);            
            $testResult->setLiteralCrew($crewData['Competitor'] ?? null);
            $testResult->setStatus($crewData['Status'] ?? false); 

            $em->persist($testResult);
            $results[] = $testResult;
        }

        $em->flush();

        return new JsonResponse([
            'status' => 'ok',
            'imported' => count($results),
        ]);
    }

       #[Route('/3rdparty/trackanalyzer/import-ANR-scores', name: 'import_trackanalyzer_ANR_scores', methods: ['POST'])]
    public function importResultsANRScores(
        Request $request,
        EntityManagerInterface $em,
        TestsRepository $repositoryTest,
        CrewsRepository $repositoryCrew,
        LoggerInterface $logger
    ): JsonResponse {
        $authHeader = $request->headers->get('Authorization');
        $rawJson = $request->getContent();

        $logger->info('TrackAnalyzer import called', [
            'Authorization' => $authHeader,
        ]);

        $data = json_decode($rawJson, true);
        if (!$data) {
            $logger->error('Invalid JSON received', ['raw' => $rawJson]);
        }
        $logger->debug('Parsed JSON:', $data);

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return new JsonResponse(['error' => 'Missing or malformed Authorization header'], 401);
        }
        
        /** @var \App\Entity\Users $user */
        $user = $this->getUser(); 
        $logger->info('Import called by user', [
            'email' => $user?->getEmail() ?? 'unknown',
        ]);

        $data = json_decode($request->getContent(), true);
        if (!$data || empty($data['testId']) || empty($data['Crews']) || !is_array($data['Crews'])) {
            return new JsonResponse(['error' => 'Invalid JSON structure'], 400);
        }

        $test = $repositoryTest->findOneBy(['code' => $data['testId']]); 
        if (!$test) {
            return new JsonResponse(['error' => 'Code de l\'épreuve inconnu : ' . $data['testId']], 404);
            exit();
        }


        $results = [];

        foreach ($data['Crews'] as $crewData) {
            if (empty($crewData['CrewId'])) {
                $logger->warning('Id du concurrent non trouvé', ['CrewId' => $crewData['CrewId']]);
                continue;
            }

            $crew = $repositoryCrew->find($crewData['CrewId']);
            if (!$crew) {
                $logger->warning('Concurrent non trouvé', ['CrewId' => $crewData['CrewId']]);
                continue;
            }
            $existingResults = $em->getRepository(TestResults::class)->findBy([
                'test' => $test,
                'crew' => $crew->getId()
            ]);

            foreach ($existingResults as $result) {
                $em->remove($result);
            }

            if (!empty($existingResults)) {
                $em->flush();
            }
            $testResult = new TestResults();
            $testResult->setTest($test);                
            $testResult->setCrew($crew);
            $testResult->setCategory($crew->getCategory()->value);                          
            $testResult->setNavigation($crewData['Navigation'] ?? null);
            $testResult->setLanding($crewData['Landing'] ?? null);                     
            $testResult->setStatus($crewData['Status'] ?? false); 

            $em->persist($testResult);
            $results[] = $testResult;
        }

        $em->flush();

        return new JsonResponse([
            'status' => 'ok',
            'imported' => count($results),
        ]);
    }
}