<?php
// src/Service/CompetitionScoringService.php

namespace App\Service;

use App\Entity\Competitions;
use Psr\Log\LoggerInterface;
use App\Repository\TestsRepository;
;

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

        foreach ($competition->getTests() as $test) {
            $testId = $test->getId();

            foreach ($test->getTestResults() as $result) {
                $category = $result->getCategory();
                if (!in_array($category, ['Elite', 'Honneur'])) {
                    continue;
                }

                // Identifiant d'équipage : id si crew existe, sinon literal
               if ($result->getCrew()) {
                    $crew = $result->getCrew();

                    // Préparer un affichage texte directement utilisable
                    if (method_exists($crew, 'getFullName') && $crew->getFullName()) {
                        $crewValue = $crew->getFullName();
                    } else {
                        // fallback sur Pilot/Navigator si pas de fullName
                        $pilot = method_exists($crew, 'getPilot') ? $crew->getPilot() : '';
                        $navigator = method_exists($crew, 'getNavigator') ? $crew->getNavigator() : '';
                        if ($competition->getTypecompetition()?->getId() === 2) {
                            // Précision : seulement le pilote
                            $crewValue = $pilot;
                        } else {
                            // Rallye / ANR : pilote / navigateur
                            $crewValue = trim($pilot . '/' . $navigator);
                        }
                    }

                    $key = $crew->getId();
                } else {
                    // LiteralCrew
                    $key = $result->getLiteralCrew();
                    $crewValue = $result->getLiteralCrew();
                }

                if (!isset($scoreByCategory[$category][$key])) {
                    $scoreByCategory[$category][$key] = [
                        'crew' => $crewValue,
                        'tests' => [],
                        'total' => 0,
                        'dns' => false,
                    ];
                }

                // Did Not Start ?
                $dns = $result->isDns() ?? false;

                if ($dns) {
                    $nav = $att = $sum = null;
                } else {
                    switch ($typeId) {
                        case 2: // Précision
                            $nav = $this->computeNullableSum([
                                $result->getNavigation(),
                                $result->getObservation(),
                                $result->getFlightPlanning(),
                            ]);

                            $att = $result->getLanding() !== null ? (int) $result->getLanding() : null;

                            $sum = ($nav === null && $att === null)
                                ? null
                                : (($nav ?? 0) + ($att ?? 0));
                            break;

                        case 1: // Rallye
                            $nav = $this->computeNullableSum([
                                $result->getNavigation(),
                                $result->getObservation(),
                                $result->getLanding(),
                            ]);

                            $att = null;
                            $sum = $nav;
                            break;

                        case 3: // ANR
                        default:
                            $nav = $this->computeNullableSum([
                                $result->getNavigation(),
                            ]);

                            $att = $result->getLanding() !== null ? (int) $result->getLanding() : null;

                            $sum = ($nav === null && $att === null)
                                ? null
                                : (($nav ?? 0) + ($att ?? 0));
                            break;
                    }
                }

                $scoreByCategory[$category][$key]['tests'][$testId] = [
                    'nav' => $nav,
                    'att' => $att,
                    'total' => $sum,
                    'dns' => $dns,
                ];
            }
        }
        // Calcul total général et flag DNS pour le classement final
        foreach ($scoreByCategory as &$list) {
            foreach ($list as &$crewData) {
                $total = 0;
                $hasDnsTest = false;

                foreach ($crewData['tests'] as $testResult) {
                    if ($testResult['dns']) {
                        $hasDnsTest = true;
                    } elseif ($testResult['total'] !== null) {
                        $total += $testResult['total'];
                    }
                }

                $crewData['total'] = $hasDnsTest ? null : $total;
                $crewData['dns'] = $hasDnsTest;
            }
        }

        // Tri final : DNS en bas
        foreach ($scoreByCategory as &$list) {
            uasort($list, function ($a, $b) {
                if (($a['dns'] ?? false) && !($b['dns'] ?? false)) return 1;
                if (!($a['dns'] ?? false) && ($b['dns'] ?? false)) return -1;
                if (($a['dns'] ?? false) && ($b['dns'] ?? false)) return 0;
                return $a['total'] <=> $b['total'];
            });
        }
        $this->logger->debug('calc test', [
        'testId' => $testId,
        'crewKey' => $key,
        'typeId' => $typeId,
        'nav' => $nav,
        'att' => $att,
        'total' => $sum
        ]);

        return $scoreByCategory;
    }

    public function calculateLiveScores(
       int $testId,
    ): array {
        $scoreByCategory = [
            'Elite' => [],
            'Honneur' => [],
        ];
        $test = $this->testsRepository->find($testId);
        $competition = $test->getCompetition();
        $typeId = $competition->getTypecompetition()?->getId();

        foreach ($test->getTestResults() as $result) {
            $category = $result->getCategory();
            if (!in_array($category, ['Elite', 'Honneur'])) {
                continue; // on ignore les autres catégories
            }

            // === Étape 1 : On extrait les différentes composantes du score ===
            // Pour le type "Précision"
            if ($typeId === 2) {
                $navParts = [
                    $result->getNavigation(),
                    $result->getObservation(),
                    $result->getFlightPlanning(),
                ];
                $attPart = $result->getLanding();

                $hasNav = array_filter($navParts, fn($val) => $val !== null);
                $hasAtt = $attPart !== null;

                $nav = !empty($hasNav) ? array_sum(array_map(fn($v) => $v ?? 0, $navParts)) : null;
                $att = $hasAtt ? $attPart : null;
                $sum = ($nav ?? 0) + ($att ?? 0);

            } else { // Pour les autres types (ex: Rallye, ANR)
                $parts = [
                    $result->getNavigation(),
                    $result->getObservation(),
                    $result->getLanding(),
                ];
                $hasValues = array_filter($parts, fn($val) => $val !== null);

                $nav = !empty($hasValues) ? array_sum(array_map(fn($v) => $v ?? 0, $parts)) : null;
                $att = $result->getLanding() ?? null;
                $sum = $nav ?? 0;
            }

            // === Étape 2 : Identifier l’équipage ===
            if ($result->getCrew()) {
                $key = $result->getCrew()->getId();     // identifiant unique de l’équipage
                $crewValue = $result->getCrew();        // entité complète
            } else {
                $key = $result->getLiteralCrew();       // nom texte si pas d’entité
                $crewValue = $result->getLiteralCrew();
            }

            // === Étape 3 : Initialisation de l’équipage dans la catégorie ===
            if (!isset($scoreByCategory[$category][$key])) {
                $scoreByCategory[$category][$key] = [
                    'crew' => $crewValue,
                    'tests' => [],
                    'total' => 0,
                ];
            }

            // === Étape 4 : Enregistrement du score du test ===
            $scoreByCategory[$category][$key]['tests'][$testId] = [
                'nav' => $nav,
                'att' => $att,
                'total' => ($nav !== null || $att !== null) ? $sum : null,
            ];

            // === Étape 5 : Ajout au total général (si test réellement couru) ===
            if ($nav !== null || $att !== null) {
                $scoreByCategory[$category][$key]['total'] += $sum;
            }
        }

        // Sort each category
        foreach ($scoreByCategory as &$list) {
            uasort($list, fn($a, $b) => $a['total'] <=> $b['total']);
        }

        return $scoreByCategory;
    }

    public function calculateAggregateScores(Competitions $competition): array
    {
        $scoreByCategory = [
            'Elite' => [],
            'Honneur' => [],
        ];

        $typeId = (int) $competition->getTypecompetition()?->getId();

        foreach ($competition->getTests() as $test) {
            foreach ($test->getTestResults() as $result) {
                $category = $result->getCategory();
                if (!in_array($category, ['Elite', 'Honneur'])) {
                    continue;
                }

                // Identifiant de l’équipage / pilote
                if ($result->getCrew()) {
                    $crew = $result->getCrew();
                    $crewValue = method_exists($crew, 'getFullName') && $crew->getFullName()
                        ? $crew->getFullName()
                        : trim((method_exists($crew, 'getPilot') ? $crew->getPilot() : '') . '/' . (method_exists($crew, 'getNavigator') ? $crew->getNavigator() : ''));

                    $key = $crew->getId();
                } else {
                    $key = $result->getLiteralCrew();
                    $crewValue = $result->getLiteralCrew();
                }

                if (!isset($scoreByCategory[$category][$key])) {
                    $scoreByCategory[$category][$key] = [
                        'crew' => $crewValue,
                        'nav' => null,
                        'obs' => null,
                        'att' => null,
                        'total' => null,
                    ];
                }

                // DNS
                $dns = $result->isDns() ?? false;

                if ($dns) {
                    $nav = $obs = $att = $sum = null;
                } else {
                    switch ($typeId) {
                        case 2: // Précision
                            $nav = $this->computeNullableSum([
                                $result->getNavigation(),
                                $result->getObservation(),
                            ]);
                            $att = $result->getFlightPlanning() ?? 0;
                            $sum = ($nav ?? 0) + ($att ?? 0);
                            break;

                        case 1: // Rallye
                            $nav = $this->computeNullableSum([
                                $result->getNavigation(),
                                $result->getObservation(),
                            ]);
                            $att = $result->getLanding() ?? 0;
                            $sum = ($nav ?? 0) + ($att ?? 0);
                            break;

                        case 3: // ANR
                        default:
                            $nav = $result->getNavigation() ?? null;
                            $obs = $result->getObservation() ?? null;
                            $att = $result->getLanding() ?? 0;
                            $sum = ($nav ?? 0) + ($obs ?? 0) + ($att ?? 0);
                            break;
                    }
                }

                $scoreByCategory[$category][$key]['nav'] = $nav;
                $scoreByCategory[$category][$key]['obs'] = $obs ?? 0;
                $scoreByCategory[$category][$key]['att'] = $att;
                $scoreByCategory[$category][$key]['total'] = $sum;
            }
        }

        // Tri par total décroissant et DNS en bas
        foreach ($scoreByCategory as &$list) {
            uasort($list, function ($a, $b) {
                if (($a['total'] === null) && ($b['total'] !== null)) return 1;
                if (($a['total'] !== null) && ($b['total'] === null)) return -1;
                return ($a['total'] ?? 0) <=> ($b['total'] ?? 0);
            });
        }

        return $scoreByCategory;
    }
}
