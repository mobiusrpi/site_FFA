<?php 
// src/Controller/TrackAnalyzerController.php
namespace App\Controller;

use App\Entity\TestResults;
use Psr\Log\LoggerInterface;
use App\Entity\TestStartOrder;
use App\Repository\CrewsRepository;
use App\Repository\TestsRepository;
use Psr\Cache\CacheItemPoolInterface;
use App\Service\TrackAnalyzerImporter;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TestStartOrderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TrackanalyzerController extends AbstractController
{
    public function __construct( 
        private TrackAnalyzerImporter $importer,
        private LoggerInterface $logger)
    {
        $this->importer = $importer;
        $this->logger = $logger;
    }

/**
 * Import scores live results function
 *
 * @param Request $request
 * @param CacheItemPoolInterface $cache
 * @param EntityManagerInterface $em
 * @param TestsRepository $repositoryTest
 * @param CrewsRepository $repositoryCrew
 * @return JsonResponse
 */
    #[Route('/3rdparty/trackanalyzer/import-live-data', name: 'import_trackanalyzer_live_data', methods: ['POST'])]
    public function importLiveData(
        Request $request,
        CacheItemPoolInterface $cache,
        EntityManagerInterface $em,
        TestsRepository $repositoryTest,
        CrewsRepository $repositoryCrew,
    ): JsonResponse {
        $authHeader = $request->headers->get('Authorization');
        $rawJson = $request->getContent();

        $this->logger->info('TrackAnalyzer import called', [
            'Authorization' => $authHeader,
            'Raw JSON' => $rawJson,
        ]);
        $data = json_decode($rawJson, true);
        if (!$data) {
            $this->logger->error('Invalid JSON received', ['raw' => $rawJson]);
        }
        $this->logger->debug('Parsed JSON:', $data);

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return new JsonResponse(['error' => 'Missing or malformed Authorization header'], 401);
        }

        $token = trim(substr($authHeader, 7));
        $item = $cache->getItem('trackanalyzer_token_' . $token);
        if (!$item->isHit()) {
            return new JsonResponse(['error' => 'Invalid or expired token'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data || empty($data['TestId']) || empty($data['Crews']) || !is_array($data['Crews'])) {
            return new JsonResponse(['error' => 'Invalid JSON structure'], 400);
        }

        $test = $repositoryTest->findOneBy(['code' => $data['TestId']]); // adjust if you use a different field
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
                $this->logger->warning('Comcurrents non trouvé', ['CrewId' => $crewData['CrewId']]);
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

/**
 * Imports results from Pipper function
 *
 * @param Request $request
 * @param EntityManagerInterface $em
 * @param TestsRepository $testsRepository
 * @param CrewsRepository $crewsRepository
 * @return JsonResponse
 */
    #[Route('/3rdparty/trackanalyzer/import-results-data', name: 'import_trackanalyzer_results_data', methods: ['POST'])]
    public function importResultsData(
        Request $request,
        EntityManagerInterface $em,
        TestsRepository $testsRepository,
        CrewsRepository $crewsRepository,
    ): JsonResponse {
        $authHeader = $request->headers->get('Authorization');
        $rawJson = $request->getContent();

        $this ->logger->info('TrackAnalyzer import called', [
            'Authorization' => $authHeader,
        ]);

        $data = json_decode($rawJson, true);
        if (!$data) {
            $this ->logger->error('Invalid JSON received', ['raw' => $rawJson]);
        }
        $this->logger->debug('Parsed JSON:', $data);

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return new JsonResponse(['error' => 'Missing or malformed Authorization header'], 401);
        }
        
        /** @var \App\Entity\Users $user */
        $user = $this->getUser(); 
        $this->logger->info('Import called by user', [
            'email' => $user?->getEmail() ?? 'unknown',
        ]);

        $data = json_decode($request->getContent(), true);
        if (!$data || empty($data['TestId']) || empty($data['Crews']) || !is_array($data['Crews'])) {
            return new JsonResponse(['error' => 'Invalid JSON structure'], 400);
        }

        $test = $testsRepository->findOneBy(['code' => $data['TestId']]); 
        if (!$test) {
            return new JsonResponse(['error' => 'Code de l\'épreuve inconnu : ' . $data['TestId']], 404);
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
        $invalidCrew = [];        
        $importedCrew = [];

        foreach ($data['Crews'] as $crewData) {
            if ($crewData['Status'] == true) {
                if (empty($crewData['CrewId'])) {
                    continue;
                }

                $crew = $crewsRepository->find($crewData['CrewId']);
                if (!$crew) {
                    $this->logger->warning('Comcurrents non trouvé', ['CrewId' => $crewData['CrewId']]);
                    $invalidCrew[] = $crewData['CrewId'];
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
        $importedCrew[] = $crewData['CrewId'];
        $em->flush();

        return new JsonResponse([
            'status' => 'ok',
            'imported' => count($results),
            'invalidCrew' => $invalidCrew,
        ]);
    }

/**
 * Import test results scores ANR function
 *
 * @param Request $request
 * @return JsonResponse
 */
    #[Route('/3rdparty/trackanalyzer/import-ANR-scores', name: 'import_trackanalyzer_ANR_scores', methods: ['POST'])]
    public function importResultsANRScores(Request $request): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return new JsonResponse(['error' => 'Missing or malformed Authorization header'], 401);
        }
        $rawJson = $request->getContent(); // ← ce que Symfony a reçu

        $data = json_decode($rawJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Malformed JSON: ' . json_last_error_msg(), ['raw' => $rawJson]);
            return new JsonResponse(['error' => 'Malformed JSON: ' . json_last_error_msg()], 400);
        }

        $result = $this->importer->importResults($data, false);
        if (isset($result['error'])) {
            return new JsonResponse(['error' => $result['error']], 400);
        }

        return new JsonResponse($result);
    }
    
/**
 * Import test results scores function
 *
 * @param Request $request
 * @return JsonResponse
 */
    #[Route('/3rdparty/trackanalyzer/import-test-scores', name: 'import_trackanalyzer_test_scores', methods: ['POST'])]
    public function importResultsScores(Request $request): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return new JsonResponse(['error' => 'Missing or malformed Authorization header'], 401);
        }
        $rawJson = $request->getContent(); // ← ce que Symfony a reçu

        $data = json_decode($rawJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Malformed JSON: ' . json_last_error_msg(), ['raw' => $rawJson]);
            return new JsonResponse(['error' => 'Malformed JSON: ' . json_last_error_msg()], 400);
        }

        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid or empty JSON'], 400);
        }

        $result = $this->importer->importResults($data, true);
        if (isset($result['error'])) {
            return new JsonResponse(['error' => $result['error']], 400);
        }

        return new JsonResponse($result);
    }


 /**
 * Get test competitors function
 *
 * @param string $testCode
 * @param Request $request
 * @param CrewsRepository $crewsRepository
 * @param Security $security
 * @return JsonResponse
 */
   #[Route('/3rdparty/trackanalyzer/test/{testCode}/crews', name: 'trackanalyzer_test_crews', methods: ['GET'])]
    public function getOrCreateStartOrder(
        string $testCode,
        TestsRepository $testsRepository,
        TestStartOrderRepository $startOrderRepository,
        CrewsRepository $crewsRepository,
        EntityManagerInterface $em,
    ): JsonResponse {

        $test = $testsRepository->findOneBy(['code' => $testCode]);

        if (!$test) {
            throw $this->createNotFoundException("Épreuve non trouvée pour le code $testCode");
        }

        $existingOrders = $startOrderRepository->findBy(['test' => $test], ['startOrder' => 'ASC']);

        if (count($existingOrders) > 0) {
            $data = array_map(function (TestStartOrder $order) {
                $crew = $order->getCrew();
                return [
                    'id' => $order->getCrew()->getId(),
                    'startOrder' => $order->getStartOrder(),
                    'group' => $order->getCrewGroup(),
                    'pilot' => $crew->getPilot()?->getFullName(),
                    'navigator' => $crew->getNavigator()?->getFullName(),
                    'category' => $crew->getCategory()?->value,
                    'callsign' => $crew->getCallsign(),
                    'speed' => $crew->getAircraftSpeed()?->value,
                    'takeOffTime' => $order->getTakeOffTime()?->format('H:i'),
                ];
            }, $existingOrders);

            return $this->json(['crews' => $data]);
        }

        $crews = $crewsRepository->findCrewsByTestCode($testCode);

        $orders = [];
        $index = 1;

        foreach ($crews as $crew) {
            $order = new TestStartOrder();
            $order->setTest($test);
            $order->setCrew($crew);
            $order->setStartOrder($index++);
            $em->persist($order);
            $orders[] = $order;
        }

        $em->flush();

        // Retourner les nouveaux ordres
        $data = array_map(function (TestStartOrder $order) {                      
            $crew = $order->getCrew();          
            return [
                'id' => $order->getCrew()->getId(),
                'startOrder' => $order->getStartOrder(),
                'group' => $order->getCrewGroup(),
                'pilot' => $crew->getPilot()?->getFullName(),
                'navigator' => $crew->getNavigator()?->getFullName(),
                'category' => $crew->getCategory()?->value,
                'callsign' => $crew->getCallsign(),
                'speed' => $crew->getAircraftSpeed()?->value,
                'takeOffTime' => $order->getTakeOffTime()?->format('H:i'),
            ];
        }, $orders);

        return $this->json(['crews' => $data]);
    }

/**
 * update the start order function
 *
 * @param Request $request
 * @param EntityManagerInterface $em
 * @return JsonResponse
 */
    #[Route('/3rdparty/trackanalyzer/update-start-order', name: 'trackanalyzer_update_start_order', methods: ['POST'])]
    public function updateStartOrder(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $rawJson = $request->getContent();
        $this->logger->debug('JSON received: ' , ['raw' => $rawJson]);
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Malformed JSON: ' . json_last_error_msg(), ['raw' => $rawJson]);
            return new JsonResponse(['error' => 'Malformed JSON: ' . json_last_error_msg()], 400);
        }
        foreach ($data as $item) {        
            $testStartOrder = $em->getRepository(TestStartOrder::class)->findOneByCrewId($item['id']);            
            $this->logger->debug('Looking for crew id ' . $item['id']);
            if ($testStartOrder) {
                if ($testStartOrder) {
                    $this->logger->debug('Found TestStartOrder for crew id ' . $item['id']);
                }
                $testStartOrder->setStartOrder($item['order']);                
                $testStartOrder->setCrewGroup($item['group']);

                if (!empty($item['takeOffTime'])) {
                    $date = new \DateTimeImmutable('today');
                    [$hour, $minute] = explode(':', $item['takeOffTime']);
                    $takeOffTime = $date->setTime((int)$hour, (int)$minute);
                    $testStartOrder->setTakeOffTime($takeOffTime);
                }
            }        
        }

        $em->flush();

        return $this->json(['status' => 'ok']);
    }

   #[Route('/3rdparty/trackanalyzer/getCompetition/{testCode}/tests', name: 'trackanalyzer_get_competition', methods: ['GET'])]
    public function getCompetitionAndActiveTest(
        string $testCode,
        TestsRepository $testsRepository,
        TestStartOrderRepository $startOrderRepository,
    ): JsonResponse {
        $test = $testsRepository->findOneBy(['code' => $testCode]);
        if (!$test) {
            throw $this->createNotFoundException("Épreuve non trouvée pour le code $testCode");
        }
        $orderCount = $startOrderRepository->countByTest($test);
        $competition = $test->getCompetition();

        return $this->json([
            'competition' => [
                'id' => $competition->getId(),
                'name' => $competition->getName(),                
                'test' => $test->getName(),                
                'orderCount' => $orderCount,
            ]
        ]);
    }
};