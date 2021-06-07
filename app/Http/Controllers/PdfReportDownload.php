<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;

class PdfReportDownload extends Controller
{
    public function pdfTest(): \Illuminate\Http\Response
    {
        $pdf = \PDF::loadView("prueba");
        return $pdf->download('test.pdf');
    }
}
