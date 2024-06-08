<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Helpers\Extracto;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Concerns\WithMappedCells;
//MODELS
use App\Models\Portafolio\Nits;
use App\Models\Sistema\Inmueble;
use App\Models\Sistema\InmuebleNit;
use App\Models\Sistema\CuotasMultasImport;
use App\Models\Sistema\ConceptoFacturacion;

class CutasExtrasImport implements ToCollection, WithHeadingRow, WithProgressBar
{
    use Importable;

    public function collection(Collection $rows)
    {
        $columna = 2;
        foreach ($rows as $row) {
            // dd($row);
            $estado = 0;
            $observacion = '';

            $nit = null;
            $inmueble = null;
            $conceptoFacturacion = null;
            $fechaFin = $row['fecha_fin'] ? Date::excelToDateTimeObject($row['fecha_fin']) : '';
            $fechaInicio = $row['fecha_inicio'] ? Date::excelToDateTimeObject($row['fecha_inicio']) : Carbon::now();

            if (!$row['cod_concepto'] && !$row['inmueble'] && !$row['cedula_nit'] && !$row['valor']) {
                continue;
            }

            if ($row['cod_concepto']) {
                $concepto = ConceptoFacturacion::where('codigo', $row['cod_concepto'])->first();
                if ($concepto) {
                    $conceptoFacturacion = $concepto;
                }  else {
                    $estado = 1;
                    $observacion.= 'El concepto de facturacion: '.$row['cod_concepto'].', no fue encontrado!<br>';
                }
            } else {
                $estado = 1;
                $observacion.= 'El concepto de facturacion es requerido!<br>'; 
            }

            if ($row['inmueble']) {
                $inmueble = Inmueble::with('zona')
                    ->where('nombre', $row['inmueble'])
                    ->first();
                if ($inmueble) {
                    $inmuebleNit = InmuebleNit::with('nit')
                        ->where('id_inmueble', $inmueble->id)
                        ->first();
                    
                    if ($inmuebleNit->nit) {
                        $nit = $inmuebleNit->nit;
                    } else {
                        $estado = 1;
                        $observacion.= 'El inmueble: '.$row['inmueble'].', no tiene propietario!<br>'; 
                    }
                } else {
                    $estado = 1;
                    $observacion.= 'El inmueble: '.$row['inmueble'].', no fue encontrado!<br>';
                }
            }

            if ($row['cedula_nit']) {
                $nitDocumento = Nits::where('numero_documento', $row['cedula_nit'])->first();

                if (!$nitDocumento) {
                    $estado = 1;
                    $observacion.= 'El numero de documento: '.$row['cedula_nit'].', no fue encontrado!<br>';
                }
                if (!$nit) {
                    $nit = $nitDocumento;
                } else if ($nitDocumento && $nit->id != $nitDocumento->id) {
                    $estado = 1;
                    $observacion.= 'El numero de documento: '.$row['cedula_nit'].', no coincide con el propietario!<br>';
                }
            }

            CuotasMultasImport::create([
                'id_inmueble' => $inmueble ? $inmueble->id : null,
                'id_nit' => $nit ? $nit->id : null,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'id_concepto_facturacion' => $conceptoFacturacion ? $conceptoFacturacion->id : '',
                'numero_documento' => $row['cedula_nit'],
                'nombre_inmueble' => $inmueble ? $inmueble->nombre : '',
                'nombre_nit' => $nit ? $nit->nombre_completo : '',
                'valor_total' => $row['valor'],
                'observacion' => $estado ? $observacion : 'Listo para importar',
                'estado' => $estado,
            ]);
        }
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function mapping(): array
    {
        return [
            'cod_concepto'  => 'A1',
            'inmueble'  => 'B1',
            'cedula_nit' => 'C1',
            'fecha_inicio' => 'D1',
            'fecha_fin' => 'E1',
            'valor' => 'F1',
        ];
    }

}
