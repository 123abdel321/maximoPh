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
use App\Models\Sistema\Zonas;
use App\Models\Portafolio\Nits;
use App\Models\Sistema\Entorno;
use App\Models\Sistema\Inmueble;
use App\Models\Sistema\InmuebleNit;
use App\Models\Sistema\InmueblesImport;
use App\Models\Sistema\ConceptoFacturacion;

class InmueblesGeneralesImport implements ToCollection, WithHeadingRow, WithProgressBar
{
    use Importable;

    protected $actualizar_valores = null;

    public function __construct(string $actualizar_valores)
    {
        $this->actualizar_valores = $actualizar_valores;
    }

    public function collection(Collection $rows)
    {
        $columna = 2;
        foreach ($rows as $row) {
            
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
            $coheficiente = null;

            if ($row['inmueble']) {
                //BUSCAR INMUEBLE
                if ($zona) {
                    $inmueble = Inmueble::with('zona')
                        ->where('nombre', $row['inmueble'])
                        ->where('id_zona', $zona->id)
                        ->first();
                } else {
                    $inmueble = Inmueble::with('zona')
                        ->where('nombre', $row['inmueble'])
                        ->first();
                }
                if (!$inmueble) {
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

            if ($row['zona']) {
                $zona = Zonas::where('nombre', $row['zona'])->first();

                if (!$inmueble && !$zona) {
                    $estado = 1;
                    $observacionMala.= 'La zona: '.$row['zona'].', no fue encontrada! <br>';
                }
            } else if ($inmueble) {
                $zona = Zonas::find($inmueble->id_zona)->first();
            }

            if ($row['cedula_nit']) {
                $nit = Nits::where('numero_documento', $row['cedula_nit'])->first();
                if ($nit) {
                    if ($inmuebleNit && $inmuebleNit->nit && $inmuebleNit->nit->numero_documento != $row['cedula_nit']) {
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

            if ($row['tipo']) {
                if ($row['tipo'] != '0' &&  $row['tipo'] != '1' && $row['tipo'] != '2') {
                    $estado = 1;
                    $observacionMala.= 'El tipo de propietario: '.$row['concepto'].', es incorrecto! <br>';
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
            if ($row['coheficiente']) {
                $coheficiente = $row['coheficiente'];
            } else if ($area) {
                $coheficiente = $area / $area_total_m2;
            } else if ($inmueble) {
                $coheficiente = $inmueble->coheficiente;
            }

            if ($this->actualizar_valores && !$inmueble) {
                $estado = 1;
                $observacionMala.= 'El inmueble no existe para actualizar precio!<br>'; 
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
                'coheficiente' => $coheficiente,
                'porcentaje_aumento' => $row['aumento'],
                'valor_aumento' => $row['valor_aumento'],
                'nombre_nit' => $nit ? $nit->nombre_completo : '',
                'numero_documento' => $row['cedula_nit'],
                'tipo' => $row['tipo'],
                'porcentaje_administracion' => $row['admin'],
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

}
