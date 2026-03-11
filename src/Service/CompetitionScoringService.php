<?php
// src/Service/CompetitionScoringService.php

namespace App\Service;

use Psr\Log\LoggerInterface;
use App\Entity\Competitions;
use App\Entity\Enum\Category;
use App\Repository\TestsRepository;

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
                ];
            }
        }

        // --- Calcul du total général et flag DNS/DNF ---
        foreach ($scoreByCategory as &$list) {
            foreach ($list as &$crewData) {
                $total = 0;
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
                    if ($t['total'] !== null) {
                        $total += $t['total'];
                    }
                }

                $crewData['total'] = $status !== 0 ? null : $total;
                $crewData['dns'] = $status;
            }
        }

        // --- Tri final : DNS en bas ---
        foreach ($scoreByCategory as &$list) {
            uasort($list, function ($a, $b) {
                $aOut = $a['dns'] !== 0;
                $bOut = $b['dns'] !== 0;

                if ($aOut !== $bOut) {
                    return $aOut ? 1 : -1;
                }

                return ($a['total'] ?? PHP_INT_MAX) <=> ($b['total'] ?? PHP_INT_MAX);
            });
        }

        return $scoreByCategory;
    }

    public function calculateScoresLive(Competitions $competition, int $typeId): array
    {
        $scoreByCategory = [
            'Elite' => [],
            'Honneur' => [],
        ];

        foreach ($competition->getTests() as $test) {
            // Tous les tests, même non validés
            foreach ($test->getTestResults() as $result) {
                $crew = $result->getCrew();
                if (!$crew) {
                    continue; // pas de literalCrew pour le live
                }

                $categoryEnum = $crew->getCategory();
                if (!$categoryEnum instanceof Category || !in_array($categoryEnum, [Category::Elite, Category::Honneur], true)) {
                    continue;
                }
                $category = $categoryEnum->value ?? null;                

                $key = $crew->getId();

                if (!isset($scoreByCategory[$category][$key])) {
                    if ($crew) {
                        $pilotName = trim(
                            ($crew->getPilot()?->getLastname() ?? '') . ' ' . ($crew->getPilot()?->getFirstname() ?? '')
                        );

                        if ($typeId === 2) { // Solo
                            $crewValue = $pilotName;
                        } else { // Équipage complet
                            $navigatorName = trim(
                                ($crew->getNavigator()?->getLastname() ?? '') . ' ' . ($crew->getNavigator()?->getFirstname() ?? '')
                            );
                            $crewValue = "$pilotName - $navigatorName";
                        }
                    } else {
                        // Pas de crew, cas import literalCrew
                        $crewValue = $result->getLiteralCrew();
                    }

                    $scoreByCategory[$category][$key] = [
                        'crew'  => $crewValue,
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

                // Si DNS/DNF, total reste inchangé
                if ($status === 0) {
                    $sum = $nav + ($typeId != 3 ? $obs : 0) + $att + ($typeId == 2 ? $fp : 0);
                    $scoreByCategory[$category][$key]['tests'][$test->getId()] = [
                        'nav'   => $nav,
                        'obs'   => $obs,
                        'att'   => $att,
                        'fp'    => $fp,
                        'total' => $sum,
                        'dns'   => $status,
                    ];
                    $scoreByCategory[$category][$key]['total'] += $sum;
                } else {
                    $scoreByCategory[$category][$key]['tests'][$test->getId()] = [
                        'nav'   => null,
                        'obs'   => null,
                        'att'   => null,
                        'fp'    => null,
                        'total' => null,
                        'dns'   => $status,
                    ];
                    $scoreByCategory[$category][$key]['dns'] = $status;
                }
            }
        }

        // Trier DNS en bas
        foreach ($scoreByCategory as &$list) {
            uasort($list, function($a, $b) {
                $aOut = $a['dns'] !== 0;
                $bOut = $b['dns'] !== 0;
                if ($aOut !== $bOut) return $aOut ? 1 : -1;
                return ($a['total'] ?? PHP_INT_MAX) <=> ($b['total'] ?? PHP_INT_MAX);
            });
        }

        return $scoreByCategory;
    }
    
    public function calculateAggregateScores(array $results, int $typeId): array
    {
        $scoresByCategory = [];

        foreach ($results as $result) {
            $crew = $result->getCrew();
            if ($crew) {
                $crewName = $crew->getPilot()->getFullName(); // ou autre méthode pour Rallye/ANR
                $crewId = $crew->getId();
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
                    'nav'            => null,
                    'obs'            => null,
                    'att'            => null,
                    'flightPlanning' => null,
                    'total'          => 0,
                    'dns'            => false,
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
            usort($categoryScores, function ($a, $b) {
                if ($a['dns'] && !$b['dns']) return 1;
                if (!$a['dns'] && $b['dns']) return -1;
                return $a['total'] <=> $b['total']; // ordre croissant
            });
        }

        return $scoresByCategory;
    }


}
