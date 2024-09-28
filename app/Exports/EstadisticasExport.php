<?php

namespace App\Exports;

use DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
//MODEL
use App\Models\Informes\InfEstadisticaDetalle;

class EstadisticasExport implements FromView, WithColumnWidths, WithStyles, WithColumnFormatting, ShouldQueue
{
    use Exportable;

    protected $id_estadistica;

    public function __construct(int $id)
	{
		$this->id_estadistica = $id;
	}

    public function view(): View
	{
		return view('excel.estadisticas.estadisticas', [
			'estadisticas' => InfEstadisticaDetalle::whereIdEstadisticas($this->id_estadistica)->get()
		]);
	}

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('1')->getFont()->setBold(true);
    }

    public function headings(): array
    {
        return [
            'Cedula',
            'Nombre',
            'Ubicación',
            'Saldo anterior',
            'Intereses',
            'Factura',
            'Total factura',
            'Total abono',
            'Saldo final'
        ];
    }

    public function columnFormats(): array
    {
        return [
			'D' => NumberFormat::FORMAT_CURRENCY_USD,
			'E' => NumberFormat::FORMAT_CURRENCY_USD,
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
			'B' => 35,
			'C' => 25,
			'D' => 20,
			'E' => 20,
			'F' => 20,
			'G' => 20,
			'H' => 20,
			'I' => 20,
        ];
	}
}
