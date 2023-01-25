<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    //instancier une classe DomPDF
    private $domPDF;

    public function __construct()
    {
        $this->domPDF = new domPDF();

        $pdfOptions = new Options();

        $pdfOptions->set('defaultFont', 'Garamond');

        $this->domPDF->setOptions($pdfOptions);
    }

    public function showPdfFile($html)
    {
        $this->domPDF->loadHtml($html);
        $this->domPDF->render();
        $this->domPDF->stream("details.pdf", [
            'Attachement' => false
        ]);
    }

    public function generateBinaryPdf($html)
    {
        $this->domPDF->loadHtml($html);
        $this->domPDF->render();
        $this->domPDF->output();
    }
}