<?php 
// src/Controller/TrackAnalyzerController.php
namespace App\Controller;

use App\Entity\Crews;
use App\Entity\Tests;
use App\Entity\Competitions;
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
            return new JsonResponse(['error' => 'Invalid JSON structure'], 200);
        }

        $test = $repositoryTest->findOneBy(['code' => $data['TestId']]); // adjust if you use a different field
        if (!$test) {
            return new JsonResponse(['error' => 'Code de l\'épreuve inconnu'], 200);
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
        
        $this->logger->debug('Parsed JSON', ['data' => $data]);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Malformed JSON: ' . json_last_error_msg(), ['raw' => $rawJson]);
            return new JsonResponse([
                'error' => 'Malformed JSON',
                'message' => json_last_error_msg(),
                'raw' => $rawJson // utile pour debug Delphi
            ], 200);
        }

        $result = $this->importer->importResultsData($data, false);

        if (isset($result['error'])) {
            $this->logger->debug('Message d\erreur :', $result);
            return new JsonResponse([
                'success' => false,
                'error' => $result['error'],
                'details' => $result['details'] ?? null
            ]);
        }

        return new JsonResponse($result);
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
    public function importResults(
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

        if (!$data || empty($data['TestId']) || empty($data['Crews']) || !is_array($data['Crews'])) {
            return new JsonResponse(['error' => 'Invalid JSON structure'], 200);
        }

        $test = $testsRepository->findOneBy(['code' => $data['TestId']]); 
        if (!$test) {
            return new JsonResponse(['error' => 'Code de l\'épreuve inconnu : ' . $data['TestId']], 200);
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
            $importedCrew[] = $crewData['CrewId'];
        }

        $em->flush();

        return new JsonResponse([
            'status' => 'ok',
            'imported' => count($results),
            'invalidCrew' => $invalidCrew,
        ]);
    }

/**
 * Import test results scores function
 *
 * @param Request $request
 * @return JsonResponse
 */
    #[Route('/3rdparty/trackanalyzer/import-test-scores', name: 'import_trackanalyzer_test_scores', methods: ['POST'])]
    public function importResultsScores(Request $request, LoggerInterface $logger,): JsonResponse
    {
        $rawJson = $request->getContent(); // ← ce que Symfony a reçu
        $logger->debug('JSON DATA', [
            'json' => $rawJson,
        ]);  
        
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return new JsonResponse(['error' => 'Missing or malformed Authorization header'], 401);
        }

        $data = json_decode($rawJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Malformed JSON: ' . json_last_error_msg(), ['raw' => $rawJson]);
            return new JsonResponse(['error' => 'Malformed JSON: ' . json_last_error_msg()], 200);
        }

        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid or empty JSON'], 200);
        }

        $result = $this->importer->importResultsData($data, true);
        if (isset($result['error'])) {
            return new JsonResponse(['error' => $result['error']], 200);
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
        
        // 1️⃣ Récupérer les ordres existants
        $existingOrders = $startOrderRepository->findBy(['test' => $test], ['startOrder' => 'ASC']);
        $existingCrewIds = array_map(fn(TestStartOrder $o) => $o->getCrew()->getId(), $existingOrders);

        // 2️⃣ Récupérer tous les crews actuels pour ce test
        $currentCrews = $crewsRepository->findCrewsByTestCode($testCode);
        $currentCrewIds = array_map(fn($crew) => $crew->getId(), $currentCrews);

        // 3️⃣ Supprimer les crews qui ne sont plus là
        $orderNeedsReview = false;
        foreach ($existingOrders as $order) {
            if (!in_array($order->getCrew()->getId(), $currentCrewIds, true)) {
                $em->remove($order);
                $orderNeedsReview = true; // On a supprimé → ordre à revoir
            }
        }

        // 4️⃣ Ajouter les nouveaux crews
        $nextOrder = count($existingOrders) + 1;
        foreach ($currentCrews as $crew) {
            if (!in_array($crew->getId(), $existingCrewIds, true)) {
                $order = new TestStartOrder();
                $order->setTest($test);
                $order->setCrew($crew);
                $order->setStartOrder($nextOrder++);
                $em->persist($order);
                $existingOrders[] = $order;
            }
        }

        $em->flush();

        // 5️⃣ Rafraîchir la liste après ajout/suppression
        $updatedOrders = $startOrderRepository->findBy(['test' => $test], ['startOrder' => 'ASC']);

        // 6️⃣ Retour des données
        $data = array_map(function (TestStartOrder $order) {
            $crew = $order->getCrew();
            return [
                'id' => $crew->getId(),
                'startOrder' => $order->getStartOrder(),
                'group' => $order->getCrewGroup(),
                'pilot' => $crew->getPilot()?->getFullName(),
                'navigator' => $crew->getNavigator()?->getFullName(),
                'category' => $crew->getCategory()?->value,
                'callsign' => $crew->getCallsign(),
                'speed' => $crew->getAircraftSpeed()?->value,
                'takeOffTime' => $order->getTakeOffTime()?->format('H:i'),
            ];
        }, $updatedOrders);

        return $this->json([
            'crews' => $data,
            'orderNeedsReview' => $orderNeedsReview
        ]);
    }

/**
 * update the start order function
 *
 * @param Request $request
 * @param EntityManagerInterface $em
 * @return JsonResponse
 */
    #[Route('/3rdparty/trackanalyzer/update-start-order', name: 'trackanalyzer_update_start_order', methods: ['POST'])]
    public function updateStartOrder(
        Request $request, 
        EntityManagerInterface $em
    ): JsonResponse  {
        $rawJson = $request->getContent();
        $this->logger->debug('JSON received: ' , ['raw' => $rawJson]);
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Malformed JSON: ' . json_last_error_msg(), ['raw' => $rawJson]);
            return new JsonResponse(['error' => 'Malformed JSON: ' . json_last_error_msg()], 200);
        }
        $testCode = $data['testCode'] ?? null;
        if (!$testCode) {
            $this->logger->error('From update-start-order : TestCode not found ');
            return $this->json(['error' =>'Code' . $testCode . '  not found '],200);
        }         

        $test = $em->getRepository(Tests::class)->findOneBy(['code' => $testCode]);
        if (!$test) {
            $this->logger->error('From update-start-order : Code' . $testCode . ' not found ');
            return $this->json(['error' => 'Code' . $testCode . ' not found in DB'], 200);
        }

        $items = $data['data'] ?? [];

        foreach ($items as $item) { 
            $crewId = $item['crewId'] ?? null;       
            if (!$test->getId() || !$crewId) {
                return $this->json(['error' => 'Crew missing'], 200);
            }
            $crew = $em->getRepository(Crews::class)->find($crewId);
            if (!$crew) {
                return $this->json(['error' => "Crew $crewId not found"], 200);
            }
            
            $testStartOrder = $em->getRepository(TestStartOrder::class)
                ->findOneBy(['test' => $test, 'crew' => $crew]);     

            // Create if not exist
            if (!$testStartOrder) {
                $testStartOrder = new TestStartOrder();
                $testStartOrder->setTest($test);
                $testStartOrder->setCrew($crew);
                $em->persist($testStartOrder);
                $this->logger->debug("Creating new TestStartOrder for crew $crewId");
            } else {
                $this->logger->debug("Updating existing TestStartOrder for crew $crewId");
            }
            // Update
            $testStartOrder->setStartOrder($item['order']);
            $testStartOrder->setCrewGroup($item['group'] ?? null);

            if (!empty($item['takeOffTime'])) {
                [$hour, $minute] = explode(':', $item['takeOffTime']);
                $takeOffTime = (new \DateTimeImmutable())->setTime((int)$hour, (int)$minute);
                $testStartOrder->setTakeOffTime($takeOffTime);
                $this->logger->debug("Update TakeOffTime for crew $crewId");
            } else {
                //  NULL if not define
                $this->logger->debug("Null TakeOffTime for crew $crewId");
                $testStartOrder->setTakeOffTime(null);
            }
        }

        $em->flush();

        return $this->json(['status' => 'ok']);
    }   
    
    /**
 * load competitor file function
 *
 * @param Request $request
 * @param EntityManagerInterface $em
 */
    #[Route('/3rdparty/trackanalyzer/load-competitor-files', name: 'trackanalyzer_load_competitor_files', methods: ['POST'])]
    public function loadCompetitorFiles(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {

        try {
            $competitor = $request->request->get('competitor');
            $testCode = $request->request->get('test');
            $testCode = strtoupper(trim($testCode));
            /**
             * @var UploadedFile|null $zipFile
             */
            $zipFile = $request->files->get('file');

            // Vérifications
            if (!$competitor) {
                return new JsonResponse([
                    'success' => false,
                    'error'   => 'Missing competitor'
                ], 400);
            }

            if (!$testCode) {
                return new JsonResponse([
                    'success' => false,
                    'error'   => 'Missing test code'
                ], 400);
            }

            if (!$zipFile) {
                return new JsonResponse([
                    'success' => false,
                    'error'   => 'Missing ZIP file'
                ], 400);
            }
            $test = $em
                ->getRepository(Tests::class)
                ->findOneBy([
                    'code' => $testCode
                ]);

            if (!$test) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Test not found'
                ], 404);
            }

            // Recherche compétition
            $competition = $test->getCompetition();
            if (!$competition) {
                return new JsonResponse([
                    'success' => false,
                    'error'   => 'Competition not found'
                ], 404);
            }

            $crew = $em
                ->getRepository(Crews::class)
                ->findOneBy([
                    'competition'  => $competition,
                    'competitorNum' => $competitor
                ]);

            if (!$crew) {
                return new JsonResponse([
                    'success' => false,
                    'error'   => 'Competitor not found'
                ], 404);
            }

            // Répertoire cible
            $targetDir =
                $this->getParameter('kernel.project_dir')
                . '/storage/competitions/'
                . $testCode
                . '/competitors/'
                . $competitor;

            if (!is_dir($targetDir)) {
                mkdir(
                    $targetDir,
                    0777,
                    true
                );
            }

            // Nom fichier ZIP
            $zipFilename =
                $zipFile->getClientOriginalName();

            // Sauvegarde ZIP
            $zipFile->move(
                $targetDir,
                $zipFilename
            );

            $zipPath =
                $targetDir . '/'
                . $zipFilename;

            // Décompression
            $zip = new \ZipArchive();

            if ($zip->open($zipPath) === true) {
                for (
                    $i = 0;
                    $i < $zip->numFiles;
                    $i++
                ) {
                    $entry =
                        $zip->getNameIndex($i);

                    // sécurité
                    if (
                        str_contains($entry, '..')
                    ) {
                        continue;
                    }

                    $stream =
                        $zip->getStream($entry);

                    if (!$stream) {
                        continue;
                    }

                    $content =
                        stream_get_contents($stream);

                    fclose($stream);

                    file_put_contents(
                        $targetDir . '/'
                        . basename($entry),
                        $content
                    );
                }

                $zip->close();

            } else {
                return new JsonResponse([
                    'success' => false,
                    'error'   => 'Invalid ZIP'
                ], 400);
            }

            return new JsonResponse([
                'success'     => true,
                'competition' => $competition->getCode(),
                'competitor'  => $competitor
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error'   => $e->getMessage()
            ], 500);
        }

    }   

    #[Route('/3rdparty/trackanalyzer/copy-start-order', name: 'trackanalyzer_copy_start_order', methods: ['POST'])]
    public function copyStartOrder(
        Request $request, 
        TestsRepository $testsRepository, 
        TestStartOrderRepository $startOrderRepository
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $testCode = $data['testCode'] ?? null;

        if (!$testCode) {
            return $this->json(['error' => 'TestCode missing'], 200);
        }

        // Récupérer le test courant
        $currentTest = $testsRepository->findOneBy(['code' => $testCode]);
        if (!$currentTest) {
            return $this->json(['error' => 'Test not found'], 200);
        }

        // Récupérer la compétition
        $competition = $currentTest->getCompetition();
        $tests = $competition->getTests();

        $testsArray = [];
        foreach ($tests as $test) {
            $startOrders = $startOrderRepository->findBy(['test' => $test]);

            $startListArray = [];
            foreach ($startOrders as $item) {
                $startListArray[] = [
                    'crewId'     => $item->getCrew()->getId(),
                    'startOrder' => $item->getStartOrder(),
                    'crewGroup'  => $item->getCrewGroup(),
                    'pilot'      => $item->getCrew()->getPilot()->getFullName(),
                    'navigator'  => $item->getCrew()->getNavigator()->getFullName(),
                    'category'   => $item->getCrew()->getCategory()?->value,
                    'callsign'   => $item->getCrew()->getCallsign(),
                    'speed'      => $item->getCrew()->getAircraftSpeed()?->value,
                    'takeOffTime'=> $item->getTakeOffTime()?->format('Y-m-d H:i:s')
                ];
            }

            $testsArray[] = [
                'code'          => $test->getCode(),
                'name'          => $test->getName(),
                'existingStartList' => count($startListArray),
                'startList'     => $startListArray
            ];
        }

        return $this->json([
            'competitionName' => $competition->getName(),
            'tests'           => $testsArray
        ]);
    }

    #[Route('/3rdparty/trackanalyzer/clear-start-order', name: 'trackanalyzer_clear_start_order', methods: ['POST'])]
    public function clearStartOrder(
        Request $request, 
        EntityManagerInterface $em
    ): JsonResponse  {
        $rawJson = $request->getContent();
        $this->logger->debug('JSON received: ' , ['raw' => $rawJson]);
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Malformed JSON: ' . json_last_error_msg(), ['raw' => $rawJson]);
            return new JsonResponse(['error' => 'Malformed JSON: ' . json_last_error_msg()], 200);
        }
        $testCode = $data['testCode'] ?? null;
        if (!$testCode) {
            $this->logger->error('From clear-start-order : TestCode not found ');
            return $this->json(['error' =>'Code' . $testCode . '  not found '],200);
        }         

        $test = $em->getRepository(Tests::class)->findOneBy(['code' => $testCode]);
        if (!$test) {
            $this->logger->error('From clear-start-order : Code' . $testCode . ' not found ');
            return $this->json(['error' => 'Code' . $testCode . ' not found in DB'], 200);
        }

        $qb = $em->createQueryBuilder();

        $deleted = $em->createQueryBuilder()
            ->delete(TestStartOrder::class, 'tso')
            ->where('tso.test = :test')
            ->setParameter('test', $test)
            ->getQuery()
            ->execute();

        return $this->json([
            'status'  => 'ok',
            'deleted' => $deleted
        ]);

        $em->flush();

        return $this->json([
            'status' => 'ok',
            'deleted' => $deleted
        ]);
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

    #[Route('/3rdparty/trackanalyzer/ping', methods: ['GET'])]
    public function ping(): JsonResponse
    {
        return new JsonResponse(['status' => 'ok']);
    }
};