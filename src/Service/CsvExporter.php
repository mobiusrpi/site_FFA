<?php 

namespace App\Service;

use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExporter
{
    public function exportCsv(array $rows, string $delimiter = ';'): string
    {
        $handle = fopen('php://temp', 'w+'); 

        if (!empty($rows)) {         
            fputcsv($handle, array_keys($rows[0]), $delimiter);
            foreach ($rows as $row) {
                // Ensure each field is casted to string to avoid Excel weirdness
                fputcsv($handle, array_map(fn($v) => (string) $v, $row), $delimiter);
            }
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        // Conversion LF -> CRLF pour compatibilité Windows/Excel
        $csvContent = str_replace("\n", "\r\n", $csvContent);
        // BOM pou Exel
        $csvContent = "\xEF\xBB\xBF" . $csvContent;
        
        return $csvContent;
    }
}
