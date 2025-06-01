<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;


class PdfService
{
    private $domPdf;

    public function __construct() {

        $this->domPdf = new DomPdf();

        $pdfOptions = new Options();

        $pdfOptions->set('defaultFont', 'Garamond');
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('defaultPaperSize', 'A4');
        $pdfOptions->set('defaultPaperOrientation', 'landscape');

        $this->domPdf->setOptions($pdfOptions);

    }

    public function showPdfFile($html,$fileName): Response
    {    
        $newFileName = str_replace('/', '_', $fileName);

        $this->domPdf->loadHtml($html);
        $this->domPdf->setPaper("A4", "landscape");        
        $this->domPdf->render();    

        $this->domPdf->stream( $newFileName . ".pdf", [
            'Attachement' => true
        ]);        

        exit;
        
/*         
 $output = $this->domPdf->output();
        
        return new Response(
            $output,
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$fileName.'.pdf"',
            ]
        );
    }

    public function generateBinaryPDF($html) {
        $this->domPdf->loadHtml($html);
        $this->domPdf->render();
        $this->domPdf->output(); 
        */
    }
       
}
