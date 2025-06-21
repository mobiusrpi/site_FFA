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
    #[Route('/3rdparty/trackanalyzer/import-crews-data', name: 'import_trackanalyzer_crews_data', methods: ['POST'])]
    public function importCrewsData(
        Request $request,
        CacheItemPoolInterface $cache,
        EntityManagerInterface $em,
        TestsRepository $testRepository,
        CrewsRepository $crewRepository,
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

        $test = $testRepository->findOneBy(['code' => $data['testId']]); // adjust if you use a different field
        if (!$test) {
            return new JsonResponse(['error' => 'Test not found'], 404);
        }

        $results = [];

        foreach ($data['Crews'] as $crewData) {
            if (empty($crewData['crewId'])) {
                continue;
            }

            $crew = $crewRepository->find($crewData['crewId']);
            if (!$crew) {
                continue;
            }

            $testResult = new TestResults();
            $testResult->setTest($test);
            $testResult->setCrew($crew);
            $testResult->setNavigation($crewData['nav'] ?? null);
    //        $testResult->setStatus($crewData['complaint'] ?? false);
    //                $testResult->setLanding($data['att'] ?? null);            
    //                $testResult->setObservation($data['obs'] ?? null);
    //                $testResult->setFlightPlanning($data['flightPlanning'] ?? null);


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