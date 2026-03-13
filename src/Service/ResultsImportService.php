<?php
// src/Service/ResultsImportService.php
namespace App\Service;

use App\Entity\Tests;
use App\Entity\TestResults;
use Doctrine\ORM\EntityManagerInterface;

class ResultsImportService
{
    public function __construct(
        private EntityManagerInterface $entityManager)
    {}    
    
    /**
     * Import CSV depuis un fichier pour un test donné
     */
    public function importCSVFile(\Symfony\Component\HttpFoundation\File\UploadedFile $csvFile, Tests $test): void
    {
        // Lecture et conversion UTF-8
        $rawContent = file_get_contents($csvFile->getPathname());
        $encoding = mb_detect_encoding($rawContent, ['Windows-1252', 'ISO-8859-1', 'UTF-8'], true);
        $utf8Content = mb_convert_encoding($rawContent, 'UTF-8', $encoding ?: 'UTF-8');

        $tempFilePath = tempnam(sys_get_temp_dir(), 'csv_utf8_');
        file_put_contents($tempFilePath, $utf8Content);

        $handle = fopen($tempFilePath, 'r');

        // Première ligne pour identifier la catégorie
        $firstRow = fgetcsv($handle, 0, ',');
        $firstRow = array_map(fn($v) => trim(str_replace(["\xC2\xA0", "\xA0", "\u{00A0}"], '', $v)), $firstRow);
        $category = $firstRow[4] ?? null;

        if ($category) {
            // Supprimer les anciens résultats pour ce test + catégorie
            $oldResults = $this->entityManager->getRepository(TestResults::class)->findBy([
                'test' => $test,
                'category' => $category,
            ]);

            foreach ($oldResults as $old) {
                $this->entityManager->remove($old);
            }
            $this->entityManager->flush();

            // Déterminer le type de compétition
            $type = $test->getCompetition()->getTypecompetition();
            $typeComp = $type && $type->getId() === 1;

            // Fonction de création
            switch ($typeComp) {
                case 1:
                    $createFn = [$this, 'createResultRallyFromRow'];
                    break;                
                case 2:
                    $createFn = [$this, 'createResultPPFromRow'];
                    break;                
                case 3:
                    $createFn = [$this, 'createResultANRFromRow'];
                    break;
                default:
                    return; // type inconnu, on ne fait rien];
            }

            // Première ligne
            $result = call_user_func($createFn, $firstRow, $test);
            $this->entityManager->persist($result);

            // Autres lignes
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $row = array_map(fn($v) => trim(str_replace(["\xC2\xA0", "\xA0", "\u{00A0}"], '', $v)), $row);
                $result = call_user_func($createFn, $row, $test);
                $this->entityManager->persist($result);
            }

            $this->entityManager->flush();
        }

        fclose($handle);
        unlink($tempFilePath);
    }

    /**
     * Crée un TestResults pour Rallye
     */
    private function createResultRallyFromRow(array $row, Tests $test): TestResults
    {
        $row = array_map(fn($v) => trim($v), $row);

        $result = new TestResults();
        $result->setTest($test);
        $result->setCategory($row[4]);
        $result->setLiteralCrew($row[16]);
        $result->setGender(in_array($row[17], ['M','F']) ? $row[17] : '');
        $result->setFlyingclub($row[18]);
        $result->setCommittee($row[19]);
        $result->setFlightPlanning(0);
        $result->setObservation(is_numeric($row[20]) ? (int)$row[20] : 0);
        $result->setNavigation(is_numeric($row[21]) ? (int)$row[21] : 0);
        $result->setLanding(is_numeric($row[22]) ? (int)$row[22] : 0);

        return $result;
    }

    /**
     * Crée un TestResults pour Pilotage de précision
     */
    private function createResultPPFromRow(array $row, Tests $test): TestResults
    {
        $row = array_map(fn($v) => trim($v), $row);

        $result = new TestResults();
        $result->setTest($test);
        $result->setCategory($row[4]);
        $result->setLiteralCrew($row[17]);
        $result->setGender(in_array($row[18], ['M','F']) ? $row[18] : '');
        $result->setFlyingclub($row[19]);
        $result->setCommittee($row[20]);
        $result->setFlightPlanning(is_numeric($row[21]) ? (int)$row[21] : 0);
        $result->setObservation(is_numeric($row[22]) ? (int)$row[22] : 0);
        $result->setNavigation(is_numeric($row[23]) ? (int)$row[23] : 0);
        $result->setLanding(is_numeric($row[24]) ? (int)$row[24] : 0);

        return $result;
    } 
    private function createResultANRromRow(array $row, Tests $test): TestResults
    {
        $row = array_map(fn($v) => trim($v), $row);

        $result = new TestResults();
        $result->setTest($test);
        $result->setCategory($row[1]);
        $result->setLiteralCrew($row[2]);
        $result->setGender(in_array($row[18], ['M','F']) ? $row[3] : '');
        $result->setFlyingclub($row[4]);
        $result->setCommittee($row[5]);
        $result->setFlightPlanning(0);
        $result->setObservation(0);
        $result->setNavigation(is_numeric($row[6]) ? (int)$row[6] : 0);
        $result->setLanding(is_numeric($row[7]) ? (int)$row[7] : 0);

        return $result;
    }
}