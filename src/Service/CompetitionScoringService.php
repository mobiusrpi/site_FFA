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

            foreach ($test->getTestResults() as $result) {
                $category = $result->getCategory();
                if (!in_array($category, ['Elite', 'Honneur'])) continue;

                // Calculate score
                if ($typeId === 2) {
                    $nav = ($result->getNavigation() ?? 0) + ($result->getObservation() ?? 0) + ($result->getFlightPlanning() ?? 0);
                    $att = $result->getLanding() ?? 0;
                    $sum = $nav + $att;
                } else {
                    $nav = ($result->getNavigation() ?? 0) + ($result->getObservation() ?? 0) + ($result->getLanding() ?? 0) + ($result->getFlightPlanning() ?? 0);
                    $att = $result->getLanding() ?? 0;
                    $sum = $nav;
                }

                // Group by real crew or literal
            if ($result->getCrew()) {
                $key = $result->getCrew()->getId();
                $crewValue = $result->getCrew();
            } else {
                $key = $result->getLiteralCrew();
                $crewValue = $result->getLiteralCrew();
            }

                if (!isset($scoreByCategory[$category][$key])) {
                    $scoreByCategory[$category][$key] = [
                        'crew' => $crewValue,
                        'tests' => [],
                        'total' => 0,
                ];
                }

                $scoreByCategory[$category][$key]['tests'][$testId] = [
                    'nav' => $nav,
                    'att' => $att,
                    'total' => $sum,
                ];
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
