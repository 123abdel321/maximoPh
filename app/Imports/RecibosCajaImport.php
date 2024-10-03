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
use App\Models\Sistema\Entorno;
use App\Models\Sistema\Inmueble;
use App\Models\Sistema\InmuebleNit;
use App\Models\Sistema\ConRecibosImport;
use App\Models\Sistema\ConceptoFacturacion;

class RecibosCajaImport implements ToCollection, WithHeadingRow, WithProgressBar
{
    use Importable;
    public $redondeo = null;

    public function collection(Collection $rows)
    {
        $columna = 0;
        $conceptoFacturacionSinIdentificar = Entorno::where('nombre', 'id_concepto_pago_none')->first();
        $conceptoFacturacionSinIdentificar = $conceptoFacturacionSinIdentificar ? $conceptoFacturacionSinIdentificar->valor : 0;
        $nitPorDefecto = Entorno::where('nombre', 'id_nit_por_defecto')->first();
        $this->redondeo = Entorno::where('nombre', 'redondeo_intereses')->first();
        $this->redondeo = $this->redondeo ? $this->redondeo->valor : 0;
        $nitPorDefecto = $nitPorDefecto ? $nitPorDefecto->valor : 0;
        foreach ($rows as $key => $row) {            

            $estado = 0;
            $observacion = '';

            $nit = null;
            $inmueble = null;
            $inmuebleNit = null;
            $conceptoFacturacion = null;
            $saldoNuevo = 0;
            $saldoTotal = 0;
            $descuentoProntoPago = 0;
            $anticipo = 0;
            $valorPendiente = 0;
            $extractoSaldo = 0;
            
            if (!$row['inmueble'] && !$row['cedula_nit'] && !$row['valor']) {
                continue;
            }

            if (!$row['inmueble'] && !$row['cedula_nit'] && $row['valor']) {
                $conceptoFacturacion = ConceptoFacturacion::where('id', $conceptoFacturacionSinIdentificar)->first();
                $nit = Nits::where('id', $nitPorDefecto)->first();
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
                $concepto = ConceptoFacturacion::where('codigo', $row['cedula_nit'])->first();
                $nitConcepto = Nits::where('email', $row['email'])->first();
                
                if (!$nitDocumento && !$concepto && $conceptoFacturacionSinIdentificar && $nitConcepto) {
                    $conceptoFacturacion = ConceptoFacturacion::where('id', $conceptoFacturacionSinIdentificar)->first();
                    $conceptoFacturacion = $concepto;
                    $nit = $nitConcepto;
                } else if ($concepto && $nitConcepto) {
                    $conceptoFacturacion = $concepto;
                    $nit = $nitConcepto;
                } else {
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
            }

            if ($row['valor']) {
                if ($nit) {
                    $inicioMes =  Carbon::parse($fechaManual)->format('Y-m');
                    $inicioMes = $inicioMes.'-01';
                    $finMes = Carbon::parse($fechaManual)->format('Y-m-t');
                    $facturaDescuento = $this->getFacturaMes($nit->id, $inicioMes, $fechaManual);

                    $pagoTotal = floatval($row['valor']);

                    if (!$conceptoFacturacion) {

                        $extracto = (new Extracto(
                            $nit->id,
                            [3,7],
                            null,
                            $fechaManual
                        ))->completo()->first();

                        $extractoCXC = (new Extracto(
                            $nit->id,
                            [4,8],
                            null,
                            $fechaManual
                        ))->completo()->first();
                            
                        $extractoCXC = $extractoCXC ? $extractoCXC->saldo : 0;

                        if ($extracto && $extracto->saldo) {
                            $valorPendiente = $extracto->saldo;
                            $prontoPago = 0;

                            $descuentoProntoPago = $this->calcularTotalDescuento($facturaDescuento, $extracto, $pagoTotal, $extractoCXC);
                            $pagoTotal+= $descuentoProntoPago;
                            $pagoTotal+= $extractoCXC;
                            if (($valorPendiente - $pagoTotal) < 0) {
                                $anticipo+= $pagoTotal - $extracto->saldo;
                            }
                        } else {
                            $anticipo+= floatval($row['valor']);
                        }
                    }

                    $extractoSaldo = (new Extracto(
                        $nit->id,
                        [3,7]
                    ))->completo()->first();

                    $extractoCXP = (new Extracto(
                        $nit->id,
                        [4,8]
                    ))->completo()->first();

                    $extractoSaldo = $extractoSaldo ? $extractoSaldo->saldo : 0;
                    $extractoSaldo-= $extractoCXP ? $extractoCXP->saldo : 0;
                    $anticipo+= $extractoCXP ? $extractoCXP->saldo : 0;
                }
            }

            if (!$conceptoFacturacion) {
                $saldoNuevo = $anticipo ? 0 : $valorPendiente - floatval($row['valor']);
            }

            ConRecibosImport::create([
                'id_inmueble' => $inmueble ? $inmueble->id : null,
                'id_concepto_facturacion' => $conceptoFacturacion ? $conceptoFacturacion->id : null,
                'id_nit' => $nit ? $nit->id : null,
                'fecha_manual' => $fechaManual,
                'codigo' => (string)$row['inmueble'],
                'numero_documento' => $row['cedula_nit'],
                'nombre_inmueble' => $inmueble ? $inmueble->nombre : '',
                'nombre_zona' => $inmueble ? $inmueble->zona->nombre : '',
                'nombre_nit' => $nit ? $nit->id.'_'.$nit->numero_documento.': '.$nit->nombre_completo : '',
                'numero_concepto_facturacion' => $conceptoFacturacion ? $conceptoFacturacion->codigo.' - '.$conceptoFacturacion->nombre_concepto : '',
                'email' => $row['email'],
                'pago' => $row['valor'],
                'descuento' => $descuentoProntoPago,
                'saldo' => $extractoSaldo,
                'saldo_nuevo' => $saldoNuevo,
                'anticipos' => $anticipo,
                'observacion' => $estado ? $observacion : 'Listo para importar',
                'estado' => $estado
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
            'email' => 'E1',
        ];
    }

    private function calcularTotalDescuento($facturaDescuento, $extracto, $totalPago, $extractoCXC)
    {   
        if ($facturaDescuento && !$facturaDescuento->has_pronto_pago) {
            if ($totalPago + $facturaDescuento->descuento + $extractoCXC >= $extracto->saldo) {
                return $facturaDescuento->descuento;
            }
        }
        return 0;
    }

    private function getFacturaMes($id_nit, $inicioMes, $fechaManual)
    {
        $fechaManual = Carbon::parse($fechaManual)->format("Y-m-d");
        
        $facturas = DB::connection('max')->select("SELECT
                FA.id AS id_factura,
                FD.id AS id_factura_detalle,
                FA.pronto_pago AS has_pronto_pago,
                FD.id_concepto_facturacion,
                FD.id_cuenta_por_cobrar,
                CF.id_cuenta_gasto,
                FD.documento_referencia,
                SUM(FD.valor) AS subtotal,
                CASE
                    WHEN CF.dias_pronto_pago > DATEDIFF('{$fechaManual}', '{$inicioMes}')
                        THEN SUM(FD.valor) * (CF.porcentaje_pronto_pago / 100)
                        ELSE 0
                END AS descuento,
                CASE
                    WHEN CF.dias_pronto_pago > DATEDIFF('{$fechaManual}', '{$inicioMes}')
                        THEN SUM(FD.valor) - (SUM(FD.valor) * (CF.porcentaje_pronto_pago / 100))
                        ELSE SUM(FD.valor)
                END AS valor_total
                
            FROM
                facturacion_detalles FD
                
            LEFT JOIN facturacions FA ON FD.id_factura = FA.id
            LEFT JOIN concepto_facturacions CF ON FD.id_concepto_facturacion = CF.id

            WHERE FD.id_nit = $id_nit
                AND FA.id IS NOT NULL
                AND FD.fecha_manual = '{$inicioMes}'
                AND CF.porcentaje_pronto_pago > 0
                AND FA.pronto_pago IS NULL
                AND CF.dias_pronto_pago > DATEDIFF('{$fechaManual}', '{$inicioMes}')
                
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

        $data->descuento = $this->roundNumber($data->descuento);

        return $data;
    }

    private function roundNumber($number)
    {
        if ($this->redondeo) {
            return round($number / $this->redondeo) * $this->redondeo;
        }
        return $number;
    }

}
