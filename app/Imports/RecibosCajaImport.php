<?php

namespace App\Imports;

use DB;
use Carbon\Carbon;
use App\Helpers\Extracto;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\Importable;
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
use App\Models\Sistema\Entorno;
use App\Models\Empresa\Empresa;
use App\Models\Sistema\Inmueble;
use App\Models\Sistema\InmuebleNit;
use App\Models\Sistema\ConRecibosImport;
use App\Models\Sistema\ConceptoFacturacion;

class RecibosCajaImport implements ToCollection, WithValidation, SkipsOnFailure, WithChunkReading, WithHeadingRow, WithProgressBar
{
    use Importable, SkipsFailures;

    public $empresa = null;
    public $redondeo = null;

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
        
        $columna = 0;
        $conceptoFacturacionSinIdentificar = Entorno::where('nombre', 'id_concepto_pago_none')->first();
        $conceptoFacturacionSinIdentificar = $conceptoFacturacionSinIdentificar ? $conceptoFacturacionSinIdentificar->valor : 0;
        $nitPorDefecto = Entorno::where('nombre', 'id_nit_por_defecto')->first();
        $this->redondeo = Entorno::where('nombre', 'redondeo_intereses')->first();
        $this->redondeo = $this->redondeo ? $this->redondeo->valor : 0;
        $nitPorDefecto = $nitPorDefecto ? $nitPorDefecto->valor : 0;
        $fechaCargaArchivos = Carbon::now()->format('Y-m-d H:i:s');

        foreach ($rows as $key => $row) {

            if (!count($row)) continue;

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
            
            $fechaManual = $this->parseFecha($row['fecha_manual']);
            if (!$fechaManual) {
                $observacion.= 'La fecha: '.$row['fecha_manual'].', no tiene el formato correcto!<br>';
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
                
                $concepto = ConceptoFacturacion::where('codigo', $row['cedula_nit'])->first();
                $nitConcepto = null;
                if ($row['email']) {
                    $nitConcepto = Nits::where('email', $row['email'])->first();
                } else {
                    $nitConcepto = Nits::where('id', $nitPorDefecto)->first();
                }

                $nitDocumento = Nits::where('numero_documento', $row['cedula_nit'])
                    ->whereRaw('LENGTH(numero_documento) = ?', [strlen($row['cedula_nit'])])
                    ->first();
                
                if (!$nitDocumento && $nitConcepto && ($concepto || $conceptoFacturacionSinIdentificar)) {
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
            
            $faltanteDescuento = 0;
            $descuentoProntoPago = 0;

            if ($row['valor'] && $fechaManual) {
                if ($nit) {
                    $inicioMes =  Carbon::parse($fechaManual)->format('Y-m');
                    $inicioMes = $inicioMes.'-01';
                    $inicioMesMenosDia = Carbon::parse($inicioMes)->subDay()->format('Y-m-d');
                    $finMes = Carbon::parse($fechaManual)->format('Y-m-t');
                    $facturaDescuento = $this->getFacturaMes($nit->id, $inicioMes, $fechaManual);

                    $pagoTotal = floatval($row['valor']);
                    
                    if ($this->existeRegistro($nit->id, $fechaManual, $pagoTotal, $fechaCargaArchivos)){
                        $estado = 1;
                        $observacion.= 'El numero de documento: '.$row['cedula_nit'].', ya tiene un pago con el valor: '.$row['valor'].', en el día: '.$fechaManual.'!<br>';
                    } else if (!$conceptoFacturacion) {

                        $sandoPendiente = (new Extracto(
                            $nit->id,
                            [3,7],
                            null,
                            $inicioMesMenosDia
                        ))->completo()->first();

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
                        $valorPendiente = $extracto ? $extracto->saldo : 0;

                        if ($extracto && $extracto->saldo && !$extractoCXC) {
                            $prontoPago = 0;

                            [$descuentoProntoPago, $faltanteDescuento] = $this->calcularTotalDescuento($facturaDescuento, $extracto, $pagoTotal, $extractoCXC);
                            
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
                'faltante_descuento' => $faltanteDescuento,
                'saldo' => $extractoSaldo,
                'saldo_nuevo' => $saldoNuevo,
                'anticipos' => $anticipo,
                'observacion' => $estado ? $observacion : 'Listo para importar',
                'estado' => $estado
            ]);
        }
    }

    public function prepareForValidation($data, $index)
    {
        $fileHeaders = array_keys($data);
        $requiredHeaders = ['inmueble', 'cedula_nit', 'fecha_manual', 'valor', 'email'];
        
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

    protected function isEmptyRow($row)
    {
        return empty(array_filter($row, function($value) {
            return !is_null($value) && $value !== '';
        }));
    }

    private function calcularTotalDescuento($facturaDescuento, $extracto, $totalPago, $extractoCXC)
    {
        $descuento = ($facturaDescuento && property_exists($facturaDescuento, 'descuento')) ? $facturaDescuento->descuento : 0;

        if ($facturaDescuento && !$facturaDescuento->has_pronto_pago) {
            if ($totalPago + $descuento + $extractoCXC >= $extracto->saldo) {
                return [$descuento, 0];
            }
        }
        $faltante = $extracto->saldo - ($totalPago + $descuento + $extractoCXC);
        return [0, $faltante < 0 ? 0 : $faltante];
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
                        THEN ROUND(SUM(FD.valor) * (CF.porcentaje_pronto_pago / 100), 0)
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
                AND FD.naturaleza_opuesta = 0
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

    private function parseFecha($fecha, $hora = null)
    {
        $fechaObj = null;
        
        // Parsear la fecha
        if ($fecha && str_contains($fecha, '/')) {
            $fechaObj = Carbon::parse($fecha);
        } else if ($fecha && str_contains($fecha, '-')) {
            $fechaObj = Carbon::parse($fecha);
        } else if (is_numeric($fecha)) {
            $fechaObj = Carbon::instance(Date::excelToDateTimeObject($fecha));
        }
        
        if (!$fechaObj) {
            return null;
        }
        
        // Formatear la fecha base
        $fechaFormateada = $fechaObj->format('Y-m-d');
        
        // Si hay hora, agregarla
        if (isset($hora)) {
            try {
                if (is_numeric($hora)) {
                $horaObj = Carbon::instance(Date::excelToDateTimeObject($hora));
                
                } else {
                    // Intenta parsear la hora en diferentes formatos comunes
                    $horaObj = Carbon::createFromFormat('H:i:s', $hora) ?:
                            Carbon::createFromFormat('H:i', $hora) ?:
                            Carbon::parse($hora);
                }
                
                $horaFormateada = $horaObj->format('H:i:s');
                return $fechaFormateada . ' ' . $horaFormateada;
            } catch (\Exception $e) {
                return $fechaFormateada;
            }
        }
        return $fechaFormateada;
    }

    private function roundNumber($number)
    {
        if ($this->redondeo) {
            // Primero redondeamos a 2 decimales para evitar problemas con números flotantes
            $number = round($number, 2);
            // Luego aplicamos el redondeo al múltiplo más cercano
            return round($number / $this->redondeo) * $this->redondeo;
        }
        return $number;
    }

    public function existeRegistro($id_nit = null, $fecha = null, $valor = null, $fecha_limite = null)
    {
        $fechaHoy = Carbon::now();
        $fecha = Carbon::parse($fecha)->format('Y-m-d');
        $id_comprobante_recibos_caja = Entorno::where('nombre', 'id_comprobante_recibos_caja')->first()->valor;
        $id_comprobante_recibos_caja = $id_comprobante_recibos_caja ?? 1;
        return DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                "N.id AS id_nit",
                "TD.nombre AS tipo_documento",
                "N.numero_documento",
                "N.id_ciudad",
                DB::raw("(CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, otros_nombres, primer_apellido, segundo_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                "N.razon_social",
                "N.telefono_1",
                "N.telefono_2",
                "N.email",
                "N.direccion",
                "N.plazo",
                "PC.id AS id_cuenta",
                "PC.cuenta",
                "PC.nombre AS nombre_cuenta",
                "DG.documento_referencia",
                "DG.id_centro_costos",
                "CC.codigo AS codigo_cecos",
                "CC.nombre AS nombre_cecos",
                "DG.id_comprobante AS id_comprobante",
                "CO.codigo AS codigo_comprobante",
                "CO.nombre AS nombre_comprobante",
                "CO.tipo_comprobante",
                "DG.consecutivo",
                "DG.concepto",
                "DG.fecha_manual",
                "DG.created_at",
                "PC.naturaleza_ingresos",
                "PC.naturaleza_egresos",
                "PC.naturaleza_compras",
                "PC.naturaleza_ventas",
                "PC.naturaleza_cuenta",
                "PC.exige_nit",
                "PC.exige_documento_referencia",
                "PC.exige_concepto",
                "PC.exige_centro_costos",
                DB::raw("DG.debito AS debito"),
                DB::raw("DG.credito AS credito"),
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('plan_cuentas_tipos AS PCT', 'DG.id_cuenta', 'PCT.id_cuenta')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->leftJoin('tipos_documentos AS TD', 'N.id_tipo_documento', 'TD.id')
            ->where('anulado', 0)
            // ->whereIn('PCT.id_tipo_cuenta', [2])
            ->when($id_nit ? $id_nit : false, function ($query) use($id_nit) {
				$query->where('N.id', $id_nit);
			})
            ->when($fecha ? $fecha : false, function ($query) use($fecha) {
				$query->where('DG.fecha_manual', $fecha);
			})
            ->when($id_comprobante_recibos_caja ? $id_comprobante_recibos_caja : false, function ($query) use($id_comprobante_recibos_caja) {
				$query->where('DG.id_comprobante', $id_comprobante_recibos_caja);
			})
            ->when($valor ? $valor : false, function ($query) use($valor) {
                $query->where(function ($q) use($valor) {
                    $q->where('DG.credito', $valor)
                        ->orWhere('DG.debito', $valor);
                });
			})
            ->when($fecha_limite ? $fecha_limite : false, function ($query) use($fecha_limite) {
				$query->where('DG.created_at', '<=', $fecha_limite);
			})
            ->first();
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

    public function rules(): array
    {
        return [
            '*.inmueble'  => 'nullable',
            '*.cedula_nit' => 'nullable',
            '*.fecha_manual' => 'nullable',
            '*.valor' => 'nullable',
            '*.email' => 'nullable'
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }

}
