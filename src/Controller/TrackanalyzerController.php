<?php 
// src/Controller/TrackAnalyzerController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

class TrackanalyzerController extends AbstractController
{
    #[Route('/3rdparty/trackanalyzer/import-crews-data', name: 'import_trackanalyzer_crews_data', methods: ['POST'])]
    public function importCrewsData(Request $request): JsonResponse
    {
        $expectedToken = $_ENV['AIRNODE_API_TOKEN']; 
        $authHeader = $request->headers->get('Authorization');
return new JsonResponse([
    'token_received' => $expectedToken,
    'auth_header' => $authHeader,
    'raw_json' => $request->getContent(),
]);

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return new JsonResponse(['result' => 'Missing Bearer'], 401);
        }

        $token = trim(str_replace('Bearer', '', $authHeader));

        if ($token !== $expectedToken) {
            return new JsonResponse(['result' => 'Invalid Token'], 403);
        }
           // Vérifie l'autorisation (token Bearer)
        $authHeader = $request->headers->get('Authorization');
        if ($authHeader !== 'Bearer YOUR_SECRET_TOKEN') {
            return new JsonResponse(['result' => 'Unauthorized'], 401);
        }

        $content = $request->getContent();
        $data = json_decode($content, true);

        if ($data === null || !isset($data['Crews'])) {
            return new JsonResponse(['result' => 'Invalid JSON'], 400);
        }

        foreach ($data['Crews'] as $crew) {
            $crewId = $crew['crewid'] ?? null;
            $sp = $crew['SPPenalties'] ?? 0;
            $fp = $crew['FPPenalties'] ?? 0;
            $nav = $crew['NavPenalties'] ?? 0;

            // 🔽 Traitement ici : enregistrer en BDD, mise à jour, etc.

            // Exemple de log :
            // file_put_contents('php://stderr', "Received penalties for crew $crewId: SP=$sp, FP=$fp, Nav=$nav\n", FILE_APPEND);
        }

        return new JsonResponse(['result' => 'OK']);
    }
}