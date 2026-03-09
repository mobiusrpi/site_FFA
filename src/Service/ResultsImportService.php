<?php

namespace App\Service;

use App\Entity\TestResults;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TestsRepository;
use App\Repository\CrewsRepository;

class ResultsImportService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TestsRepository $testsRepository,
        private CrewsRepository $crewsRepository,
    ) {}

    public function importCsv(int $testId, string $filePath): int
    {
        $test = $this->testsRepository->find($testId);

        if (!$test) {
            throw new \Exception('Test introuvable');
        }

        $handle = fopen($filePath, 'r');

        if (!$handle) {
            throw new \Exception('Impossible d’ouvrir le fichier');
        }

        $imported = 0;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {

            // ignorer lignes vides
            if (empty($row[0])) {
                continue;
            }

            // ID équipage (colonne Pipper)
            $crewId = (int) $row[15];

            $crew = $this->crewsRepository->find($crewId);

            if (!$crew) {
                continue;
            }

            $nav = $row[20] ?? 0;
            $obs = $row[19] ?? 0;
            $att = $row[21] ?? 0;
            $theo = $row[18] ?? 0;

            // ici tu enregistres les scores
            $testResults = new TestResults();
            $testResults->setCrew($crew);
            $testResults->setTest($test);
            $testResults->setNavigation((int)$nav);
            $testResults->setObservation((int)$obs);
            $testResults->setLanding((int)$att);
            $testResults->setFlightPlanning((int)$theo);

            $this->entityManager->persist($testResults);

            $imported++;
        }

        fclose($handle);

        $this->entityManager->flush();

        return $imported;
    }
}