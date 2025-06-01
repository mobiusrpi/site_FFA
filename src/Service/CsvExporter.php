<?php 

namespace App\Service;

use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExporter
{
    public function export(array $data, string $filename, string $delimiter = ';'): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($data, $delimiter) {
            $handle = fopen('php://output', 'w+');

            if (!empty($data)) {
                fputcsv($handle, array_keys($data[0]), $delimiter);
                foreach ($data as $row) {
                    // Ensure each field is casted to string to avoid Excel weirdness
                    fputcsv($handle, array_map(fn($v) => (string) $v, $row), $delimiter);
                }
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set(
            'Content-Disposition',
            'attachment; filename="' . $filename . '"'
        );

        return $response;
    }
}
