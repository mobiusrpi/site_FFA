<?php
// src/Service/CompetitionScoringService.php

namespace App\Service;

use App\Entity\Competitions;
use App\Enum\TestCompet;

class CompetitionScoringService
{
    public function calculateScores(Competitions $competition): array
    {
        $scoreByCategory = [
            'Elite' => [],
            'Honneur' => [],
        ];

        $typeId = $competition->getTypecompetition()?->getId();

        foreach ($competition->getTests() as $test) {
            $testId = $test->getId();

            // Pour chaque résultat lié à ce test
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
        }

        // Sort each category
        foreach ($scoreByCategory as &$list) {
            uasort($list, fn($a, $b) => $a['total'] <=> $b['total']);
        }

        return $scoreByCategory;
    }
}
