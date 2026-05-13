<?php
// src/Service/CompetitionScoringService.php

namespace App\Service;

use App\Entity\Competitions;
use App\Entity\Crews;
use App\Entity\Enum\Category;
use App\Entity\Tests;
use App\Repository\TestsRepository;
use Psr\Log\LoggerInterface;

class CompetitionScoringService
{
    public function __construct(
        private TestsRepository $testsRepository,
        private LoggerInterface $logger
    ) { 
        $this->testsRepository = $testsRepository;
        $this->logger = $logger;
    }

    /**
     * Somme nullable : retourne null si toutes les valeurs sont null,
     * sinon la somme (les valeurs null sont traitées comme 0).
     *
     * @param array $values
     * @return int|null
     */
    private function computeNullableSum(array $values): ?int
    {
        $has = false;
        $sum = 0;
        foreach ($values as $v) {
            if ($v !== null) {
                $has = true;
                $sum += (int) $v;
            }
        }
        return $has ? $sum : null;
    }

    public function calculateScores(Competitions $competition): array
    {
        $scoreByCategory = [
            'Elite' => [],
            'Honneur' => [],
        ];

        $typeId = (int) $competition->getTypecompetition()?->getId();

        $validTests = $competition->getTests()->filter(fn($t) => $t->isResultsValidated());

        if ($validTests->isEmpty()) {
        // Aucun test validé => on ne construit pas de tableau pour cette compétition
            return $scoreByCategory; // ou tu peux retourner [] pour ne rien afficher
        }

        // --- Initialisation des équipages ---
        foreach ($competition->getCrew() as $crew) {
            // Récupérer tous les résultats de ce crew pour les tests validés
            $results = $crew->getTestResults()->filter(fn($r) => $validTests->contains($r->getTest()));

            if ($results->isEmpty()) {
                // Aucun résultat => ne pas ajouter dans $scoreByCategory
                continue;
            }

            $categoryEnum = $crew->getCategory();
            if (!$categoryEnum instanceof Category) {
                continue;
            }
            if (!in_array($categoryEnum, [Category::Elite, Category::Honneur], true)) {
                continue;
            }

            $category = $categoryEnum->getLabel();
            $key      = $crew->getId();

            $pilot     = trim(($crew->getPilot()?->getLastname() ?? '') . ' ' . ($crew->getPilot()?->getFirstname() ?? ''));
            $navigator = trim(($crew->getNavigator()?->getLastname() ?? '') . ' ' . ($crew->getNavigator()?->getFirstname() ?? ''));
            $crewValue = $typeId === 2 ? $pilot : trim("$pilot - $navigator");

            $scoreByCategory[$category][$key] = [
                'crew'  => $crewValue,
                'tests' => [],
                'total' => 0,
                'dns'   => 0, // 0 = normal, -1 = DNS, 1 = DNF
                'speed' => (int) $crew->getAircraftSpeed()?->value
            ];
        }

        // --- Calcul des résultats par test ---
        foreach ($validTests as $test) {

            $testId = $test->getId();

            foreach ($test->getTestResults() as $result) {
                $crew = $result->getCrew();
                $key  = $crew?->getId() ?? $result->getLiteralCrew();

                // Catégorie
                $categoryEnum = $crew?->getCategory() ?? $result->getCategory();
                if (is_string($categoryEnum)) {
                    try {
                        $categoryEnum = Category::from($categoryEnum);
                    } catch (\ValueError) {
                        continue;
                    }
                }
                if (!$categoryEnum instanceof Category || !in_array($categoryEnum, [Category::Elite, Category::Honneur], true)) {
                    continue;
                }
                $category = $categoryEnum->getLabel();

                // Equipage
                if ($crew) {
                    $pilot     = ($crew->getPilot()?->getFullName() ?? '');
                    $navigator = ($crew->getNavigator()?->getFullName() ?? '');
                    $crewValue = $typeId === 2 ? $pilot : trim("$pilot - $navigator");
                } else {
                    $crewValue = $result->getLiteralCrew();
                }

                // Initialisation si absent
                if (!isset($scoreByCategory[$category][$key])) {
                    $scoreByCategory[$category][$key] = [
                        'crew'  => $crewValue,
                        'tests' => [],
                        'total' => 0,
                        'dns'   => 0,
                    ];
                }

                // DNS / DNF
                $status = $result->getDns() ?? 0; // 0 = normal, -1 = DNS, 1 = DNF
                $isOut  = $status !== 0;

                // Calcul des pénalités
                $nav = $att = $fp = $sum = null;
                if (!$isOut) {
                    switch ($typeId) {
                        case 2: // Précision
                            $nav = $this->computeNullableSum([$result->getNavigation(), $result->getObservation(), $result->getFlightPlanning()]);
                            $att = $result->getLanding() ?? null;
                            $sum = ($nav ?? 0) + ($att ?? 0);
                            break;

                        case 1: // Rallye
                            $nav = $this->computeNullableSum([$result->getNavigation(), $result->getObservation(), $result->getLanding()]);
                            $sum = $nav;
                            break;

                        case 3: // ANR
                        default:
                            $nav = $this->computeNullableSum([$result->getNavigation()]);
                            $att = $result->getLanding() ?? null;
                            $sum = ($nav ?? 0) + ($att ?? 0);
                    }
                }

                // Stockage
                $scoreByCategory[$category][$key]['tests'][$testId] = [
                    'nav'   => $isOut ? null : $nav,
                    'att'   => $isOut ? null : $att,
                    'total' => $isOut ? null : $sum,
                    'dns'   => $status,
                    'type'  => $test->getType()->value, 
                ];
            }
        }

        // --- Calcul du total général et flag DNS/DNF ---
        foreach ($scoreByCategory as &$list) {
            foreach ($list as &$crewData) {
                $total = 0;
                $navTheo = 0; // navigation + théorie (hors observation)
                $landing = 0; // atterrissages
                $status = 0; // 0=normal, -1=DNS, 1=DNF

                foreach ($crewData['tests'] as $t) {
                    if ($t['dns'] === 1) { // DNF
                        $status = 1;
                        break;
                    }
                    if ($t['dns'] === -1) { // DNS
                        $status = -1;
                        break;
                    }
                    if ($t['total'] === null) {
                        continue;
                    }

                    $total += $t['total'];

                    // NAV / THEO uniquement sur tests NAV*
                    if ($typeId === 2) {

                        if (in_array($t['type'] ?? null, ['nav', 'nav_att', 'nav_tg'], true)) {
                            $navTheo += ($t['nav'] ?? 0);
                        }
                    }
                    // LANDING uniquement sur tests concernés
                    if (in_array($t['type'] ?? null, ['landing', 'nav_att', 'nav_tg'], true)) {
                        $landing += ($t['att'] ?? 0);
                    }
                }
                $crewData['total'] = $status !== 0 ? null : $total;
                $crewData['navTheo'] = $status !== 0 ? null : $navTheo;
                $crewData['landing'] = $status !== 0 ? null : $landing;
                $crewData['dns'] = $status;
            }
        }

        $typeId = (int) $competition->getTypecompetition()?->getId();

        // --- Tri final : DNS en bas ---
        foreach ($scoreByCategory as &$list) {

            $logger = $this->logger;

            uasort($list, function ($a, $b) use ($typeId, $logger)  {

                // 1. DNS / DNF toujours en bas
                if ($a['dns'] !== 0 || $b['dns'] !== 0) {
                    return $a['dns'] !== 0 ? 1 : -1;
                }

                // 2. TOTAL
                $cmp = ($a['total'] ?? PHP_INT_MAX) <=> ($b['total'] ?? PHP_INT_MAX);
                if ($cmp !== 0) {
                    return $cmp;
                }
if ($typeId === 1) {

    $logger->debug('Structure complète', [
        'a' => $a,
    ]);
}
                // TYPE 1
                if ($typeId === 1) {

                    $navA = 0;
                    foreach ($a['tests'] as $test) {
                        $navA += $test['nav'] ?? 0;
                    }

                    $navB = 0;
                    foreach ($b['tests'] as $test) {
                        $navB += $test['nav'] ?? 0;
                    }

                    return $navA <=> $navB;
                }

                // TYPE 2
                if ($typeId === 2) {
                    $cmp = ($a['navTheo'] ?? PHP_INT_MAX) <=> ($b['navTheo'] ?? PHP_INT_MAX);
                    if ($cmp !== 0) {
                        return $cmp;
                    }

                    return ($a['landing'] ?? PHP_INT_MAX) <=> ($b['landing'] ?? PHP_INT_MAX);
                }

                // TYPE 3
                if ($typeId === 3) {
                    return ($a['landing'] ?? PHP_INT_MAX) <=> ($b['landing'] ?? PHP_INT_MAX);
                }

                return 0;
            });
        }

        return $scoreByCategory;
    }

     /**
     * Calcul des scores pour un live kiosk.
     *
     * @param Competitions $competition
     * @param int $typeId Type du test (1=Équipage, 2=Solo, 3=Autre)
     * @return array
     */
    public function calculateScoresLive( int $typeId, ?array $results = []): array
    {
        $scoreByCategory = [
            'Elite' => [],
            'Honneur' => [],
        ];
        foreach ($results as $result) {
            $crew = $result->getCrew();
            $categoryEnum = $crew->getCategory();
            if (!$categoryEnum instanceof Category || !in_array($categoryEnum, [Category::Elite, Category::Honneur], true)) {
                continue;
            }
            $category = $categoryEnum->value;

            $pilotName = trim($crew->getPilot()?->getFullname() ?? '');
            $navigatorName = $crew->getNavigator()?->getFullname() ?? '';

            $crewValue = $typeId === 2 ? $pilotName : "$pilotName - $navigatorName";
            $key = $crew->getId();

            if (!isset($scoreByCategory[$category][$key])) {
                $scoreByCategory[$category][$key] = [
                    'crew'  => $crew,
                    'tests' => [],
                    'total' => 0,
                    'dns'   => $result->getDns() ?? 0,
                ];
            }

            $status = $result->getDns() ?? 0;
            $nav = $result->getNavigation() ?? 0;
            $obs = $result->getObservation() ?? 0;
            $att = $result->getLanding() ?? 0;
            $fp  = $result->getFlightPlanning() ?? 0;

            $sum = ($status === 0) ? $nav + ($typeId != 3 ? $obs : 0) + $att + ($typeId == 2 ? $fp : 0) : null;

            $scoreByCategory[$category][$key]['tests'][$result->getTest()->getId()] = [
                'nav'   => $status === 0 ? $nav : null,
                'obs'   => $status === 0 ? $obs : null,
                'att'   => $status === 0 ? $att : null,
                'fp'    => $status === 0 ? $fp : null,
                'total' => $sum,
                'dns'   => $status,
            ];

            if ($status === 0) {
                $scoreByCategory[$category][$key]['total'] += $sum;
            } else {
                $scoreByCategory[$category][$key]['dns'] = $status;
            }
        }

        // Trier DNS en bas, puis par total croissant
        foreach ($scoreByCategory as &$list) {
            uasort($list, function($a, $b) {
                $aOut = $a['dns'] !== 0;
                $bOut = $b['dns'] !== 0;
                if ($aOut !== $bOut) return $aOut ? 1 : -1;
                return ($a['total'] ?? PHP_INT_MAX) <=> ($b['total'] ?? PHP_INT_MAX);
            });
        }
        $this->logger->info('TrackAnalyzer import scores', [
                'test crew elite' => $scoreByCategory[Category::Elite->value],                    
                'test crew honneur' => $scoreByCategory[Category::Honneur->value],
            ]);
        return $scoreByCategory;
    }
    
    public function calculateAggregateScores(array $results, int $typeId): array
    {
        $scoresByCategory = [];

        foreach ($results as $result) {
            $crew = $result->getCrew();
            if ($crew) {
                $pilot     = ($crew->getPilot()?->getFullName() ?? '');
                $navigator = ($crew->getNavigator()?->getFullName() ?? '');
                $crewName  = $typeId === 2 ? $pilot : trim("$pilot - $navigator");
                $crewId    = $crew->getId();
            } elseif ($result->getLiteralCrew()) {
                $crewName = $result->getLiteralCrew();
                $crewId = md5($crewName); // clé unique pour le tableau
            } else {
                continue; // pas de crew connu, ignorer
            }

            $category = $result->getCategory() ?? 'Hors Catégorie';

            if (!isset($scoresByCategory[$category][$crewId])) {
                $scoresByCategory[$category][$crewId] = [
                    'crew'           => $crewName,
                    'crewEntity'     => $crew,
                    'nav'            => null,
                    'obs'            => null,
                    'att'            => null,
                    'flightPlanning' => null,
                    'total'          => 0,
                    'dns'            => false,
                    'speed'          => $crew?->getAircraftSpeed()?->value ?? 0,
                    'tests'          => [],
                ];
            }

            $code = strtoupper(substr($result->getTest()->getCode(), 0, 3));

            switch ($code) {
                case 'NAV':
                    // Accumuler navigation
                    $scoresByCategory[$category][$crewId]['nav'] = 
                        ($scoresByCategory[$category][$crewId]['nav'] ?? 0) + ($result->getNavigation() ?? 0);

                    // Accumuler observation
                    $scoresByCategory[$category][$crewId]['obs'] = 
                        ($scoresByCategory[$category][$crewId]['obs'] ?? 0) + ($result->getObservation() ?? 0);

                    if ($typeId === 1) { 
                        $scoresByCategory[$category][$crewId]['att'] =
                                ($scoresByCategory[$category][$crewId]['att'] ?? 0) + ($result->getLanding() ?? 0);
                    }

                    if ($typeId === 2) { // Précision
                        $scoresByCategory[$category][$crewId]['flightPlanning'] = 
                            ($scoresByCategory[$category][$crewId]['flightPlanning'] ?? 0) + ($result->getFlightPlanning() ?? 0);
                    }

                    if (str_contains(strtoupper($result->getTest()->getCode()), 'ATT')) {
                        $scoresByCategory[$category][$crewId]['att'] = 
                            ($scoresByCategory[$category][$crewId]['att'] ?? 0) + ($result->getLanding() ?? 0);
                    }
                    break;

                case 'ATT':
                    $scoresByCategory[$category][$crewId]['att'] = 
                        ($scoresByCategory[$category][$crewId]['att'] ?? 0) + ($result->getLanding() ?? 0);
                    break;

                case 'ANR':
                    $scoresByCategory[$category][$crewId]['nav'] = 
                        ($scoresByCategory[$category][$crewId]['nav'] ?? 0) + ($result->getNavigation() ?? 0);
                    $scoresByCategory[$category][$crewId]['att'] = 
                        ($scoresByCategory[$category][$crewId]['att'] ?? 0) + ($result->getLanding() ?? 0);
                    break;

                default:    
                    if ($typeId === 1) { // Rallye
                        $scoresByCategory[$category][$crewId]['nav'] = 
                            ($scoresByCategory[$category][$crewId]['nav'] ?? 0) + ($result->getNavigation() ?? 0);
                        $scoresByCategory[$category][$crewId]['obs'] = 
                            ($scoresByCategory[$category][$crewId]['obs'] ?? 0) + ($result->getObservation() ?? 0);
                        $scoresByCategory[$category][$crewId]['att'] = 
                            ($scoresByCategory[$category][$crewId]['att'] ?? 0) + ($result->getLanding() ?? 0);
                    }        
                    break;
            }

            // Gestion DNS
            if ($result->isDns()) {
                $scoresByCategory[$category][$crewId]['dns'] = true;
                $scoresByCategory[$category][$crewId]['total'] = null;
            } else {
                // Recalcule du total (null si une épreuve non faite)
                $nav   = $scoresByCategory[$category][$crewId]['nav'] ?? 0;
                $obs   = $scoresByCategory[$category][$crewId]['obs'] ?? 0;
                $att   = $scoresByCategory[$category][$crewId]['att'] ?? 0;
                $fp    = $scoresByCategory[$category][$crewId]['flightPlanning'] ?? 0;

                $scoresByCategory[$category][$crewId]['total'] = $nav + $obs + $att + $fp;
            }
        }

        // Trie par catégorie → par total croissant (DNS à la fin)
        foreach ($scoresByCategory as &$categoryScores) {

            usort($categoryScores, function ($a, $b) use ($typeId) {

                // DNS à la fin
                if ($a['dns'] && !$b['dns']) {
                    return 1;
                }

                if (!$a['dns'] && $b['dns']) {
                    return -1;
                }

                // TOTAL
                $cmp = ($a['total'] ?? PHP_INT_MAX)
                    <=> ($b['total'] ?? PHP_INT_MAX);

                if ($cmp !== 0) {
                    return $cmp;
                }

                // ================= TYPE 1 =================
                if ($typeId === 1) {

                    // vitesse décroissante
                    $cmp = ($b['speed'] ?? 0)
                        <=> ($a['speed'] ?? 0);

                    if ($cmp !== 0) {
                        return $cmp;
                    }

                    // navigation croissante
                    $cmp = ($a['nav'] ?? PHP_INT_MAX)
                        <=> ($b['nav'] ?? PHP_INT_MAX);

                    if ($cmp !== 0) {
                        return $cmp;
                    }

                    return 0;
                }

                // ================= TYPE 2 =================
                if ($typeId === 2) {

                    // 1. Nav + Théorique
                    $scoreA = ($a['flightPlanning'] ?? 0)
                            + ($a['nav'] ?? 0);

                    $scoreB = ($b['flightPlanning'] ?? 0)
                            + ($b['nav'] ?? 0);

                    $cmp = $scoreA <=> $scoreB;

                    if ($cmp !== 0) {
                        return $cmp;
                    }

                    // 2. Atterrissages
                    $cmp = ($a['att'] ?? PHP_INT_MAX)
                        <=> ($b['att'] ?? PHP_INT_MAX);

                    if ($cmp !== 0) {
                        return $cmp;
                    }

                    // 3. Véritable égalité
                    return 0;
                }

                // ================= TYPE 3 =================
                if ($typeId === 3) {

                    // moins de pénalités navigation
                    $cmp = ($a['nav'] ?? PHP_INT_MAX)
                        <=> ($b['nav'] ?? PHP_INT_MAX);

                    if ($cmp !== 0) {
                        return $cmp;
                    }

                    // vraie égalité
                    return 0;
                }
            });
        }

        $rank = 0;
        $position = 0;
        $previousKey = null;

        foreach ($categoryScores as &$row) {

            if ($row['dns']) {
                $row['rank'] = '-';
                continue;
            }

            $position++;

            // clé complète d'égalité
            $currentKey = sprintf(
                '%s-%s-%s-%s-%s',
                $row['total'] ?? '',
                $row['speed'] ?? '',
                $row['nav'] ?? '',
                $row['flightPlanning'] ?? '',
                $row['att'] ?? ''
            );

            if ($currentKey !== $previousKey) {
                $rank = $position;
            }

            $row['rank'] = $rank;

            $previousKey = $currentKey;
        }

        return $scoresByCategory;
    }

    public function calculateDetailedScores(
        Competitions $competition
    ): array {

        $scoresByCategory = [];

        $validTests = $competition->getTests()->filter(
            fn($t) => $t->isResultsValidated()
        );

        foreach ($competition->getCrew() as $crew) {

            $results = $crew->getTestResults()
                ->filter(fn($r) => $validTests->contains($r->getTest()));

            if ($results->isEmpty()) {
                continue;
            }

            $categoryEnum = $crew->getCategory();

            if (!$categoryEnum instanceof Category) {
                continue;
            }

            $category = $categoryEnum->getLabel();

            $pilot = trim(
                ($crew->getPilot()?->getLastname() ?? '') . ' ' .
                ($crew->getPilot()?->getFirstname() ?? '')
            );

            $navigator = trim(
                ($crew->getNavigator()?->getLastname() ?? '') . ' ' .
                ($crew->getNavigator()?->getFirstname() ?? '')
            );

            $crewName = $competition->getTypecompetition()->getId() === 2
                ? $pilot
                : trim("$pilot - $navigator");

            $crewId = $crew->getId();

            if (!isset($scoresByCategory[$category][$crewId])) {

                $scoresByCategory[$category][$crewId] = [
                    'crew' => $crewName,
                    'crewEntity' => $crew,
                    'tests' => [],
                ];
            }

            foreach ($results as $result) {

                $test = $result->getTest();

                $scoresByCategory[$category][$crewId]['tests'][$test->getId()] = [

                    'nav' => $result->getNavigation(),
                    'obs' => $result->getObservation(),
                    'att' => $result->getLanding(),
                    'flightPlanning' => $result->getFlightPlanning(),

                    'total' =>
                        ($result->getNavigation() ?? 0)
                        + ($result->getObservation() ?? 0)
                        + ($result->getLanding() ?? 0)
                        + ($result->getFlightPlanning() ?? 0),

                    'dns' => $result->getDns(),
                ];
            }
        }

        return $scoresByCategory;
    }
}
