<?php

namespace App\Imports;

use DB;
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
            $saldoNuevo = 0;
            $saldoTotal = 0;
            $descuentoProntoPago = 0;
            $anticipo = 0;
            $valorPendiente = 0;
            
            if (!$row['inmueble'] && !$row['cedula_nit'] && !$row['valor']) {
                continue;
            }

            $fechaManual = Carbon::now();

            if ($row['fecha_manual'] && str_contains($row['fecha_manual'], '/')) {
                $fechaManual = Carbon::parse($row['fecha_manual'])->format('Y-m-d');
            } else if ($row['fecha_manual']) {
                $fechaManual = Date::excelToDateTimeObject($row['fecha_manual']);
            }


            if ($row['inmueble']) {
                $inmueble = Inmueble::with('zona')
                    ->where('nombre', (string)$row['inmueble'])
                    ->first();
                    
                if ($inmueble) {
                    $inmuebleNit = InmuebleNit::with('nit')
                        ->where('id_inmueble', $inmueble->id)
                        ->first();
                    
                    if ($inmuebleNit->nit) {
                        $nit = $inmuebleNit->nit;
                    } else {
                        $estado = 1;
                        $observacion.= 'El inmueble: '.(string)$row['inmueble'].', no tiene propietario!<br>'; 
                    }
                } else {
                    $estado = 1;
                    $observacion.= 'El inmueble: '.(string)$row['inmueble'].', no fue encontrado!<br>';
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
                    
                    $inicioMes =  Carbon::parse($fechaManual)->format('Y-m');
                    $inicioMes = $inicioMes.'-01';
                    $finMes = Carbon::parse($fechaManual)->format('Y-m-t');
                    $facturaDescuento = $this->getFacturaMes($nit->id, $inicioMes);

                    $extracto = (new Extracto(
                        $nit->id,
                        [3,7],
                    ))->completo()->first();
                    
                    $pagoTotal = floatval($row['valor']);

                    if ($extracto && $extracto->saldo) {
                        $valorPendiente = $extracto->saldo;
                        $prontoPago = 0;
                        $descuentoProntoPago = $this->calcularTotalDescuento($facturaDescuento, $extracto, $valorPendiente);
                        $pagoTotal+= $descuentoProntoPago;
    
                        if (($valorPendiente - $pagoTotal) < 0) {
                            $anticipo+= $pagoTotal - $extracto->saldo;
                        }
                    } else {
                        $anticipo+= $row['valor'];
                    }
                }
            }
            
            $saldoNuevo = $anticipo ? 0 : $valorPendiente - floatval($row['valor']);

            ConRecibosImport::create([
                'id_inmueble' => $inmueble ? $inmueble->id : null,
                'id_nit' => $nit ? $nit->id : null,
                'fecha_manual' => $fechaManual,
                'codigo' => (string)$row['inmueble'],
                'numero_documento' => $row['cedula_nit'],
                'nombre_inmueble' => $inmueble ? $inmueble->nombre : '',
                'nombre_zona' => $inmueble ? $inmueble->zona->nombre : '',
                'nombre_nit' => $nit ? $nit->nombre_completo : '',
                'pago' => $row['valor'],
                'descuento' => $descuentoProntoPago,
                'saldo' => $saldoTotal,
                'saldo_nuevo' => $saldoNuevo,
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

    private function calcularTotalDescuento($facturaDescuento, $extracto, $totalPago)
    {
        if ($facturaDescuento && $facturaDescuento->has_pronto_pago) {
            if ($totalPago + $facturaDescuento->descuento >= $extracto->saldo) {
                return $facturaDescuento->descuento;
            }
        }
        return 0;
    }

    private function getFacturaMes($id_nit, $inicioMes)
    {
        $fechaActual = Carbon::now()->format("Y-m-d");
        // dd($fechaActual, $inicioMes);
        $facturas = DB::connection('max')->select("SELECT
                FA.id AS id_factura,
                FA.pronto_pago AS has_pronto_pago,
                FD.id_concepto_facturacion,
                FD.id_cuenta_por_cobrar,
                CF.id_cuenta_gasto,
                FD.documento_referencia,
                SUM(FD.valor) AS subtotal,
                CASE
                    WHEN CF.dias_pronto_pago > DATEDIFF('{$fechaActual}', '{$inicioMes}')
                        THEN SUM(FD.valor) * (CF.porcentaje_pronto_pago / 100)
                        ELSE 0
                END AS descuento,
                CASE
                    WHEN CF.dias_pronto_pago > DATEDIFF('{$fechaActual}', '{$inicioMes}')
                        THEN SUM(FD.valor) - (SUM(FD.valor) * (CF.porcentaje_pronto_pago / 100))
                        ELSE SUM(FD.valor)
                END AS valor_total
                
            FROM
                facturacion_detalles FD
                
            LEFT JOIN facturacions FA ON FD.id_factura = FA.id
            LEFT JOIN concepto_facturacions CF ON FD.id_concepto_facturacion = CF.id

            WHERE FD.id_nit = $id_nit
                AND FD.fecha_manual = '{$inicioMes}'
                AND CF.porcentaje_pronto_pago > 0
                AND CF.dias_pronto_pago > DATEDIFF('{$fechaActual}', '{$inicioMes}')
                
            GROUP BY FD.id_cuenta_por_cobrar
        ");

        $facturas = collect($facturas);

        if (!count($facturas)) return false;

        $data = (object)[
            'id_factura' => $facturas[0]->id_factura,
            'has_pronto_pago' => $facturas[0]->has_pronto_pago,
            'subtotal' => 0,
            'descuento' => 0,
            'valor_total' => 0,
            'detalle' => []
        ];

        foreach ($facturas as $factura) {
            $data->subtotal+= $factura->subtotal;
            $data->descuento+= $factura->descuento;
            $data->valor_total+= $factura->valor_total;
            $data->detalle[$factura->id_cuenta_por_cobrar] = $factura;
        }

        return $data;
    }

}
