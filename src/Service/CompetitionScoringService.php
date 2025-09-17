<?php
// src/Service/CompetitionScoringService.php

namespace App\Service;

use App\Entity\Competitions;
use App\Repository\TestsRepository;
;

class CompetitionScoringService
{
    public function __construct(
        private TestsRepository $testsRepository,
    ) { }


public function calculateScores(Competitions $competition): array
{
    $scoreByCategory = [
        'Elite' => [],
        'Honneur' => [],
    ];

    $typeId = $competition->getTypecompetition()?->getId();

    foreach ($competition->getTests() as $test) {
        $testId = $test->getId();

        foreach ($test->getTestResults() as $result) {
            $category = $result->getCategory();
            if (!in_array($category, ['Elite', 'Honneur'])) {
                continue; // ignorer autres catégories
            }

            // Identifier l'équipage
            if ($result->getCrew()) {
                $key = $result->getCrew()->getId();
                $crewValue = $result->getCrew();
            } else {
                $key = $result->getLiteralCrew();
                $crewValue = $result->getLiteralCrew();
            }

            // Initialisation de l'équipage
            if (!isset($scoreByCategory[$category][$key])) {
                $scoreByCategory[$category][$key] = [
                    'crew' => $crewValue,
                    'tests' => [],
                    'total' => 0,
                    'dns' => false, // pour le classement général
                ];
            }

            // Calcul du score du test
            if ($result->isDns()) {
                $dns = true;
                $nav = null;
                $att = null;
                $sum = null;
            } else {
                $dns = false;

                if ($typeId === 2) { // Précision
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
                } else { // Rallye, ANR, etc.
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
            }

            // Enregistrement du test : chaque test garde son propre flag dns
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
                    $hasDnsTest = true; // au moins un test non effectué
                } elseif ($testResult['total'] !== null) {
                    $total += $testResult['total']; // somme des tests effectués
                }
            }

            // Total général : null si au moins un test DNS
            $crewData['total'] = $hasDnsTest ? null : $total;

            // Flag DNS uniquement pour le classement général
            $crewData['dns'] = $hasDnsTest;
        }
    }

    // Tri final : DNS en bas du classement général
    foreach ($scoreByCategory as &$list) {
        uasort($list, function ($a, $b) {
            if (($a['dns'] ?? false) && !($b['dns'] ?? false)) return 1;
            if (!($a['dns'] ?? false) && ($b['dns'] ?? false)) return -1;
            if (($a['dns'] ?? false) && ($b['dns'] ?? false)) return 0;

            return $a['total'] <=> $b['total'];
        });
    }

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

}
