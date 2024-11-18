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
use App\Models\Sistema\Zonas;
use App\Models\Portafolio\Nits;
use App\Models\Sistema\Entorno;
use App\Models\Sistema\Inmueble;
use App\Models\Sistema\InmuebleNit;
use App\Models\Sistema\InmueblesImport;
use App\Models\Sistema\ConceptoFacturacion;

class InmueblesGeneralesImport implements ToCollection, WithValidation, SkipsOnFailure, WithChunkReading, WithHeadingRow, WithProgressBar
{
    use Importable, SkipsFailures;

    public $empresa = null;
    protected $actualizar_valores = null;

    public function __construct($empresa, string $actualizar_valores)
    {
        $this->actualizar_valores = $actualizar_valores;
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
            $observacionMala = '';
            $observacionBuena = '';

            if (!$row['inmueble'] && !$row['cedula_nit'] && !$row['valor_admon']) {
                continue;
            }

            $nit = null;
            $zona = null;
            $area = null;
            $concepto = null;
            $inmueble = null;
            $inmuebleNit = null;
            $valor_admon = null;
            $coeficiente = null;

            if ($row['zona']) {
                $zona = Zonas::where('nombre', $row['zona'])->first();
                if (!$inmueble && !$zona) {
                    $estado = 1;
                    $observacionMala.= 'La zona: '.$row['zona'].', no fue encontrada! <br>';
                }
            } else {
                $estado = 1;
                $observacionMala.= 'La zona es requerida! <br>';
            }

            if ($row['inmueble']) {
                //BUSCAR INMUEBLE
                if ($zona) {
                    $inmueble = Inmueble::with('zona')
                        ->where('nombre', $row['inmueble'])
                        ->where('id_zona', $zona->id)
                        ->first();
                }
                
                if ($this->actualizar_valores && !$inmueble) {
                    $estado = 1;
                    $observacionMala.= 'El inmueble es requerido para la actualización! <br>';
                } else if (!$this->actualizar_valores && $inmueble) {
                    $estado = 1;
                    $observacionMala.= 'El inmueble ya se encuentra creado! <br>';
                } else if (!$inmueble) {
                    $observacionBuena.= 'Creación del inmueble! <br>';
                } else {
                    $inmuebleNit = InmuebleNit::with('nit')
                        ->where('id_inmueble', $inmueble->id)
                        ->first();
                }
            } else {
                $estado = 1;
                $observacionMala.= 'El inmueble es requerido! <br>';
            }

            if ($row['cedula_nit']) {
                $nit = Nits::where('numero_documento', $row['cedula_nit'])->first();
                
                if ($nit) {
                    if ($inmuebleNit && $inmuebleNit->nit && $inmuebleNit->nit->id == $nit->id) {
                        $observacionBuena.= 'Actualización del inmueble! <br>';
                    } else if ($inmuebleNit && $inmuebleNit->nit && $inmuebleNit->nit->id != $nit->id) {
                        $observacionBuena.= 'Actualización del propietario! <br>';
                    } else {
                        $observacionBuena.= 'Asignación del propietario!<br>';
                    }
                } else {
                    $estado = 1;
                    $observacionMala.= 'El numero de documento: '.$row['cedula_nit'].', no fue encontrado!<br>';
                }
            }

            if ($row['concepto']) {
                $concepto = ConceptoFacturacion::where('codigo', $row['concepto'])->first();
                if (!$inmueble && !$concepto) {
                    $estado = 1;
                    $observacionMala.= 'El concepto: '.$row['concepto'].', no fue encontrado! <br>';
                }
            } else if ($inmueble) {
                $concepto = ConceptoFacturacion::where('id', $inmueble->id_concepto_facturacion)
                    ->first();
            }

            if (!$this->actualizar_valores) {
                if ($row['tipo'] || $row['tipo'] == '0' ) {
                    if ($row['tipo'] != '0' &&  $row['tipo'] != '1' && $row['tipo'] != '2') {
                        $estado = 1;
                        $observacionMala.= 'El tipo de propietario: '.$row['concepto'].', es incorrecto! <br>';
                    }
                } else {
                    $estado = 1;
                    $observacionMala.= 'El tipo de usuario es requerido! <br>';
                }
            } 

            if (!$row['valor_admon'] && !$inmueble) {
                $estado = 1;
                $observacionMala.= 'El valor del nuevo inmueble es requerido! <br>';
            } else {
                $valor_admon = $row['valor_admon'];
            }

            if ($row['aumento'] && $inmueble) {
                $valor_admon = $inmueble->valor_total_administracion + ($inmueble->valor_total_administracion * ($row['aumento'] / 100));
                $observacionBuena.= 'Actualización de valor! <br>';
            }

            if ($row['valor_aumento'] && $inmueble) {
                $valor_admon = $inmueble->valor_total_administracion + $row['valor_aumento'];
                $observacionBuena.= 'Actualización de valor! <br>';
            }

            if ($row['area']) {
                $area = $row['area'];
            } else if ($inmueble) {
                $area = $inmueble->area;
            }

            $area_total_m2 = Entorno::where('nombre', 'area_total_m2')->first()->valor;
            if ($row['coeficiente']) {
                $coeficiente = $row['coeficiente'];
            } else if ($area) {
                $coeficiente = $area / $area_total_m2;
            } else if ($inmueble) {
                $coeficiente = $inmueble->coeficiente;
            }

            InmueblesImport::create([
                'id_inmueble' => $inmueble ? $inmueble->id : '',
                'id_zona' => $zona ? $zona->id : '',
                'id_nit' => $nit ? $nit->id : '',
                'id_concepto_facturacion' => $concepto ? $concepto->id : '',
                'nombre_concepto_facturacion' => $concepto ? $concepto->codigo.' '.$concepto->nombre_concepto : '',
                'nombre_inmueble' => $row['inmueble'],
                'nombre_zona' => $zona ? $zona->nombre : '',
                'area' => $area,
                'coheficiente' => $coeficiente,
                'porcentaje_aumento' => $row['aumento'],
                'valor_aumento' => $row['valor_aumento'],
                'nombre_nit' => $nit ? $nit->nombre_completo : '',
                'numero_documento' => $row['cedula_nit'],
                'tipo' => $row['tipo'],
                'porcentaje_administracion' => $row['porcentaje_admin'],
                'valor_administracion' => $valor_admon,
                'observacion' => $estado ? $observacionMala : $observacionBuena,
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
            'zona'  => 'B1',
            'concepto' => 'C1',
            'area' => 'D1',
            'coeficiente' => 'E1',
            'valor_admon' => 'F1',
            'aumento' => 'H1',
            'valor_aumento' => 'H1',
            'cedula_nit' => 'I1',
            'tipo' => 'J1',
            'porcentaje_admin' => 'K1',
        ];
    }

    public function rules(): array
    {
        return [
            '*.inmueble' => 'nullable',
            '*.zona' => 'nullable',
            '*.concepto' => 'nullable',
            '*.area' => 'nullable',
            '*.coeficiente' => 'nullable',
            '*.valor_admon' => 'nullable',
            '*.aumento' => 'nullable',
            '*.valor_aumento' => 'nullable',
            '*.cedula_nit' => 'nullable',
            '*.tipo' => 'nullable',
            '*.porcentaje_admin' => 'nullable'
        ];
    }

    public function prepareForValidation($data, $index)
    {
        $fileHeaders = array_keys($data);
        $requiredHeaders = ['inmueble', 'zona', 'concepto', 'area', 'coeficiente', 'valor_admon', 'aumento', 'valor_aumento', 'cedula_nit', 'tipo', 'porcentaje_admin'];
        
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
