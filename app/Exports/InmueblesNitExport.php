<?php

namespace App\Exports;

use DB;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
//SPREADSHEET
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
//MODEL
use App\Models\Empresa\Empresa;
use App\Models\Sistema\Inmueble;

class InmueblesNitExport implements FromView, WithColumnWidths, WithStyles, WithColumnFormatting, ShouldQueue
{
    use Exportable;

    protected $filters;
    protected $empresa;

    public function __construct(array $filters, $has_empresa)
	{
		$this->filters = $filters;
		$this->empresa = Empresa::where('token_db_maximo', $has_empresa)->first();
	}

    public function view(): View
	{
        config([
            'database.connections.max.database' => $this->empresa->token_db_maximo,
        ]);

        DB::purge('max');
        DB::reconnect('max');

        $inmuebles = Inmueble::on('max')
            ->orderBy('id', 'DESC') 
            ->with('zona', 'concepto', 'personas.nit')
            ->select(
                '*',
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'created_by',
                'updated_by'
            );

        if ($this->filters['id_nit']) {
            $inmuebles->whereHas('personas',  function ($query) {
                $query->where('id_nit', $this->filters['id_nit']);
            });
        }

        if ($this->filters['id_zona']) {
            $inmuebles->whereHas('zona',  function ($query) {
                $query->where('id_zona', $this->filters['id_zona']);
            });
        }

        if ($this->filters['id_concepto_facturacion']) {
            $inmuebles->whereHas('concepto',  function ($query) {
                $query->where('id_concepto_facturacion', $this->filters['id_concepto_facturacion']);
            });
        }

        if ($this->filters['search']) {
            $inmuebles->where('nombre', 'LIKE', '%'.$this->filters['search'].'%');
        }

		return view('excel.inmuebles-nit.inmuebles-nit', [
			'inmuebles' => $inmuebles->get(),
            'nombre_informe' => 'INMUEBLES',
            'nombre_empresa' => $this->empresa->razon_social,
            'logo_empresa' => $this->empresa->logo ? $this->empresa->logo : 'https://maximoph.co/img/logo_base.png',
		]);
	}

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('5')->getFont()->setBold(true);

        // Estilo para el nombre empresa
        $sheet->mergeCells('B1:J1');
        $sheet->getStyle('B1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 30
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Estilo para el título
        $sheet->mergeCells('B2:J2');
        $sheet->getStyle('B2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 20,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Estilo para la fecha de generación
        $sheet->mergeCells('B3:J3');
        $sheet->getStyle('B3')->applyFromArray([
            'font' => [
                'size' => 11,
                'italic' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);

        $sheet->getStyle('B4:J4')->applyFromArray([
            'font' => [
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);

        // Estilo para los encabezados (fila 7)
        $sheet->getStyle('A5:J5')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Aplica bordes finos a toda la tabla (desde la fila 7 en adelante)
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A6:J{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'Inmueble',
            'Zona',
            'Cédula',
            'Nombre',
            'Concepto',
            'Total %',
            'Area M2',
            'Coeficiente',
            'Valor admon',
            'Fecha entrega',
        ];
    }

    public function columnFormats(): array
    {
        return [
			'F' => NumberFormat::FORMAT_CURRENCY_USD,
			'G' => NumberFormat::FORMAT_CURRENCY_USD,
			'H' => NumberFormat::FORMAT_CURRENCY_USD,
			'I' => NumberFormat::FORMAT_CURRENCY_USD,
        ];
	}

    public function columnWidths(): array
    {
        return [
            'A' => 18,
			'B' => 18,
			'C' => 20,
			'D' => 35,
			'E' => 20,
			'F' => 20,
			'G' => 20,
			'H' => 20,
			'I' => 20,
			'J' => 20,
        ];
	}
}
