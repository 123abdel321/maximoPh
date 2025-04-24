<?php

namespace App\Helpers\Printers;
use Illuminate\Support\Facades\Storage;
//MODELS
use App\Models\Empresa\Empresa;

abstract class AbstractPrinterPdf
{
    public $empresa;
    public $paper;
    public $view;
    public $name;
    public $data;
    public $url;
    public $pdf;

    abstract public function view();
    abstract public function data();
    abstract public function name();
    abstract public function paper();

    public function __construct(Empresa $empresa) {
        $this->empresa = $empresa;
    }

    public function buildPdf()
    {
        $this->view = $this->view();
        $this->name = $this->name();
        $this->data = $this->data();
        $this->paper = $this->paper();

        $this->generatePdf();

        return $this;
    }

    public function generatePdf()
    {
        $this->pdf = app('dompdf.wrapper');
        $this->pdf->loadView($this->view, $this->data);
        $this->pdf->setPaper('A4', $this->paper);
    }

    public function showPdf()
    {
        return $this->pdf->stream($this->name);
    }

    public function getData()
    {
        return $this->data;
    }

    public function saveStorage()
    {
        $pdfBuilder = $this->pdf->output();
        $nameFile = '/maximo/empresas/'.$this->empresa->id.'/pdf'.'/'.$this->name.'.pdf';

        $url = Storage::disk('do_spaces')->put($nameFile, $pdfBuilder, 'public');

        return env('DO_SPACES_ENDPOINT').$nameFile;
    }
    
}
