<?php 

namespace App\Service;

class CsvExporter
{
    public function exportCsv(array $rows, string $delimiter = ';'): string
    {
        if (empty($rows)) {
            return '';
        }

        $handle = fopen('php://temp', 'w+');

        // Construire la liste complète des colonnes
        $headers = [];

        foreach ($rows as $row) {
            $headers = array_unique(array_merge($headers, array_keys($row)));
        }

        // Écrire le header UNE SEULE FOIS
        fputcsv($handle, $headers, $delimiter);

        // Écrire les lignes dans le bon ordre
        foreach ($rows as $row) {

            $orderedRow = [];

            foreach ($headers as $header) {
                $orderedRow[] = isset($row[$header]) ? (string) $row[$header] : '';
            }

            fputcsv($handle, $orderedRow, $delimiter);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        return $csvContent;
    }
}
