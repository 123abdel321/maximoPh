<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Helpers\Extracto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Concerns\WithMappedCells;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Validation\ValidationException;
//MODELS
use App\Models\Portafolio\Nits;
use App\Models\Sistema\Inmueble;
use App\Models\Sistema\InmuebleNit;
use App\Models\Sistema\CuotasMultasImport;
use App\Models\Sistema\ConceptoFacturacion;

class CutasExtrasImport implements ToCollection, WithValidation, SkipsOnFailure, WithChunkReading, WithHeadingRow, WithProgressBar
{
    use Importable, SkipsFailures;

    public $empresa = null;

    public function __construct($empresa)
    {
        $this->empresa = $empresa;
    }

    public function collection(Collection $rows)
    {
        copyDBConnection('max', 'max');
        setDBInConnection('max', $this->empresa->token_db_maximo);

        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $this->empresa->token_db_portafolio);

        $columna = 2;
        foreach ($rows as $row) {

            if (!count($row)) continue;
            
            $estado = 0;
            $observacion = '';

            if (!$row['cod_concepto'] && !$row['inmueble'] && !$row['cedula_nit'] && !$row['valor']) {
                continue;
            }

            $nit = null;
            $inmueble = null;
            $conceptoFacturacion = null;

            $fechaFin = null;
            $fechaInicio = null;
            $fechaInicioFormato = $row['fecha_inicio'];
            $fechaFinFormato = $row['fecha_fin'];

            if ($fechaInicioFormato && str_contains($fechaInicioFormato, '/')) {
                $fechaInicio = Carbon::parse($row['fecha_inicio'])->format('Y-m-d');
            } else if ($fechaInicioFormato && str_contains($fechaInicioFormato, '-')) {
                $fechaInicio = Carbon::parse($row['fecha_inicio'])->format('Y-m-d');
            } else if (is_numeric($fechaInicioFormato)) {
                $fechaInicio = Date::excelToDateTimeObject($row['fecha_inicio']);
            } else {
                $estado = 1;
                $observacion.= 'La fecha inicio: '.$fechaInicioFormato.', no tiene el formato correcto!<br>'; 
            }

            if ($fechaFinFormato && str_contains($fechaFinFormato, '/')) {
                $fechaFin = Carbon::parse($row['fecha_fin'])->format('Y-m-d');
            } else if ($fechaFinFormato && str_contains($fechaFinFormato, '-')) {
                $fechaFin = Carbon::parse($row['fecha_fin'])->format('Y-m-d');
            } else if (is_numeric($fechaFinFormato)) {
                $fechaFin = Date::excelToDateTimeObject($row['fecha_fin']);
            } else {
                $estado = 1;
                $observacion.= 'La fecha fin: '.$fechaFinFormato.', no tiene el formato correcto!<br>'; 
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

                if (!$inmueble && $nit) {
                    $inmuebleNit = InmuebleNit::with('inmueble')
                        ->where('id_nit', $nit->id)
                        ->first();

                    if ($inmuebleNit) {
                        $inmueble = $inmuebleNit->inmueble;
                    } else {
                        $estado = 1;
                        $observacion.= 'El numero de documento: '.$row['cedula_nit'].', no tiene inmuebles asociados!<br>';
                    }                    
                }
            }
            
            CuotasMultasImport::create([
                'id_inmueble' => $inmueble ? $inmueble->id : null,
                'id_nit' => $nit ? $nit->id : null,
                'fecha_inicio' => Carbon::parse($fechaInicio)->format('Y-m'),
                'fecha_fin' => Carbon::parse($fechaFin)->format('Y-m'),
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

    public function rules(): array
    {
        return [
            '*.cod_concepto' => 'nullable',
            '*.inmueble' => 'nullable',
            '*.cedula_nit' => 'nullable',
            '*.fecha_inicio' => 'nullable',
            '*.fecha_fin' => 'nullable',
            '*.valor' => 'nullable',
        ];
    }

    public function prepareForValidation($data, $index)
    {
        $fileHeaders = array_keys($data);
        $requiredHeaders = ['cod_concepto', 'inmueble', 'cedula_nit', 'fecha_inicio', 'fecha_fin', 'valor'];
        
        if ($this->isEmptyRow($data)) {
            return [];
        }

        if (array_diff($requiredHeaders, $fileHeaders)) {
            throw ValidationException::withMessages([
                'headers' => ['El archivo no tiene las cabeceras correctas: ' . implode(', ', $requiredHeaders)]
            ]);
        }

        return $data;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    protected function isEmptyRow($row)
    {
        return empty(array_filter($row, function($value) {
            return !is_null($value) && $value !== '';
        }));
    }

}
