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
use App\Models\Sistema\ConRecibosImport;

class RecibosCajaImport implements ToCollection, WithHeadingRow, WithProgressBar
{
    use Importable;

    public function collection(Collection $rows)
    {
        $columna = 2;
        foreach ($rows as $row) {
            
            $estado = 0;
            $observacion = '';

            $nit = null;
            $inmueble = null;
            $inmuebleNit = null;
            $saldoTotal = 0;
            $anticipo = 0;
            $fechaManual = $row['fecha_manual'] ? Date::excelToDateTimeObject($row['fecha_manual']): Carbon::now();

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

            if ($row['valor']) {
                if ($nit) {
                    $extracto = (new Extracto(
                        $nit->id,
                        [3,7],
                    ))->completo()->first();
    
                    $pagoTotal = $row['valor'];
                    $saldoTotal = $extracto->saldo;

                    if (($extracto->saldo - $pagoTotal) < 0) {
                        $anticipo = $pagoTotal - $extracto->saldo;
                    }
                }
            }

            ConRecibosImport::create([
                'id_inmueble' => $inmueble ? $inmueble->id : null,
                'id_nit' => $nit ? $nit->id : null,
                'fecha_manual' => $fechaManual,
                'codigo' => $row['inmueble'],
                'numero_documento' => $row['cedula_nit'],
                'nombre_inmueble' => $inmueble ? $inmueble->nombre : '',
                'nombre_zona' => $inmueble ? $inmueble->zona->nombre : '',
                'nombre_nit' => $nit ? $nit->nombre_completo : '',
                'pago' => $row['valor'],
                'saldo' => $saldoTotal,
                'saldo_nuevo' => $anticipo ? 0 : $saldoTotal - $row['valor'],
                'anticipos' => $anticipo,
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
            'inmueble'  => 'A1',
            'cedula_nit' => 'B1',
            'fecha_manual' => 'C1',
            'valor' => 'D1',
        ];
    }

}
