<?php

namespace App\Imports;

use DB;
use Carbon\Carbon;
use App\Helpers\Extracto;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
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
use App\Models\Portafolio\DocumentosGeneral;

class RecibosCajaImport implements ToCollection, WithValidation, SkipsOnFailure, WithChunkReading, WithHeadingRow, WithProgressBar
{
    use Importable, SkipsFailures;

    protected $empresa;
    protected $redondeoIntereses;
    protected $redondeoProntoPago;
    protected $id_cuenta_descuento_pronto_pago;
    protected $conceptoFacturacionSinIdentificar;
    protected $nitPorDefecto;
    protected $idComprobanteRecibosCaja;
    protected $fechaCargaArchivos;

    public function __construct($empresa)
    {
        $this->empresa = $empresa;
    }

    /**
     * Punto de entrada: procesa todas las filas del Excel.
     */
    public function collection(Collection $rows)
    {
        $this->inicializarConfiguracion();
        $this->fechaCargaArchivos = Carbon::now()->format('Y-m-d H:i:s');
        foreach ($rows as $row) {
            if (!$this->isValidRow($row->toArray())) {
                continue;
            }
            $datosFila = $this->procesarFila($row);
            ConRecibosImport::create($datosFila);
        }
    }

    /**
     * Carga toda la configuración necesaria desde la tabla Entorno.
     */
    private function inicializarConfiguracion()
    {
        copyDBConnection('max', 'max');
        setDBInConnection('max', $this->empresa->token_db_maximo);
        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $this->empresa->token_db_portafolio);

        $this->redondeoIntereses = (float) optional(Entorno::where('nombre', 'redondeo_intereses')->first())->valor ?? 0;
        $this->redondeoProntoPago = (float) optional(Entorno::where('nombre', 'redondeo_pronto_pago')->first())->valor ?? 0;
        $this->id_cuenta_descuento_pronto_pago = (int) optional(Entorno::where('nombre', 'id_cuenta_descuento_pronto_pago')->first())->valor ?? 0;
        $this->conceptoFacturacionSinIdentificar = (int) optional(Entorno::where('nombre', 'id_concepto_pago_none')->first())->valor ?? 0;
        $this->nitPorDefecto = (int) optional(Entorno::where('nombre', 'id_nit_por_defecto')->first())->valor ?? 0;
        $this->idComprobanteRecibosCaja = optional(Entorno::where('nombre', 'id_comprobante_recibos_caja')->first())->valor ?? 1;
    }

    /**
     * Procesa una fila individual: valida, resuelve NIT/inmueble, calcula descuentos y saldos.
     * Retorna un array listo para crear ConRecibosImport.
     */
    private function procesarFila($row): array
    {
        $estado = 0;
        $observacion = '';

        $fechaManual = $this->parseFecha($row['fecha_manual']);

        if (!$fechaManual) {
            $observacion .= "La fecha: {$row['fecha_manual']}, no tiene el formato correcto!<br>";
        }

        // Resolver NIT, inmueble y concepto
        $resolucion = $this->resolverNitInmuebleConcepto($row);

        $nit = $resolucion['nit'];
        $inmueble = $resolucion['inmueble'];
        $conceptoFacturacion = $resolucion['conceptoFacturacion'];
        $observacion .= $resolucion['observacion'];
        $estado = $resolucion['estado'] ? 1 : $estado;

        // Inicializar variables financieras
        $descuento = 0;
        $faltanteDescuento = 0;
        $anticipo = 0;
        $valorPendiente = 0;
        $saldoNuevo = 0;
        $extractoCXC = 0;
        $extractoSaldo = null;

        if ($nit && $fechaManual && $row['valor']) {
            $pagoTotal = (float) $row['valor'];
            $inicioMes = Carbon::parse($fechaManual)->format('Y-m-01');
            $inicioMesMenosDia = Carbon::parse($inicioMes)->subDay()->format('Y-m-d');

            // Validar duplicado
            if ($this->existeRegistro($nit->id, $fechaManual, $pagoTotal, $this->fechaCargaArchivos)) {
                $estado = 1;
                $observacion .= "El numero de documento: {$row['cedula_nit']}, ya tiene un pago con el valor: {$row['valor']}, en el día: {$fechaManual}!<br>";
            } else {
                // Obtener saldos
                $sandoPendiente = $this->obtenerSaldoPendienteHasta($nit->id, $inicioMesMenosDia);
                $extracto = $this->obtenerSaldo($nit->id, $fechaManual);
                $extractoCXC = $this->obtenerSaldoCXC($nit->id, $fechaManual);
                $valorPendiente = $extracto ? $extracto->saldo : 0;

                if (!$conceptoFacturacion) {

                    if ($extracto && $extracto->saldo) {
                        [$descuento, $faltanteDescuento] = $this->calcularDescuentoProntoPago($nit->id, $fechaManual, $pagoTotal, $extractoCXC);

                        $pagoTotal += $descuento + $extractoCXC;
                        if (($valorPendiente - $pagoTotal) < 0) {
                            $anticipo += $pagoTotal - $extracto->saldo;
                        }
                    } else {
                        $anticipo += $pagoTotal;
                    }
                }
            }

            // Saldos finales
            $extractoSaldo = $this->obtenerSaldoTotal($nit->id);
            $extractoCXP = $this->obtenerSaldoCXPTotal($nit->id);
            $anticipo += $extractoCXP ? $extractoCXP->saldo : 0;
            // Calcular nuevo saldo considerando descuento y extractoCXC
            $totalAplicado = $pagoTotal + $descuento + $extractoCXC;
            $nuevoSaldoCalculado = $valorPendiente - $totalAplicado;

            if ($nuevoSaldoCalculado < 0) {
                // Esto ya debería estar cubierto por el anticipo, pero por seguridad
                $nuevoSaldoCalculado = 0;
            }

            $saldoNuevo = $anticipo ? 0 : $nuevoSaldoCalculado;
        }

        return [
            'id_inmueble' => $inmueble ? $inmueble->id : null,
            'id_concepto_facturacion' => $conceptoFacturacion ? $conceptoFacturacion->id : null,
            'id_nit' => $nit ? $nit->id : null,
            'fecha_manual' => $fechaManual,
            'codigo' => (string) $row['inmueble'],
            'numero_documento' => $row['cedula_nit'],
            'nombre_inmueble' => $inmueble ? $inmueble->nombre : '',
            'nombre_zona' => $inmueble && $inmueble->zona ? $inmueble->zona->nombre : '',
            'nombre_nit' => $nit ? $nit->id . '_' . $nit->numero_documento . ': ' . $nit->nombre_completo : '',
            'numero_concepto_facturacion' => $conceptoFacturacion ? $conceptoFacturacion->codigo . ' - ' . $conceptoFacturacion->nombre_concepto : '',
            'email' => $row['email'],
            'pago' => $row['valor'],
            'descuento' => $descuento,
            'faltante_descuento' => $descuento ? $faltanteDescuento : 0,
            'saldo' => $extractoSaldo ? $extractoSaldo->saldo : 0,
            'saldo_nuevo' => $saldoNuevo,
            'anticipos' => $anticipo,
            'observacion' => $estado ? $observacion : 'Listo para importar',
            'estado' => $estado,
        ];
    }

    /* -------------------- MÉTODOS DE RESOLUCIÓN DE DATOS -------------------- */

    private function resolverNitInmuebleConcepto($row): array
    {
        $estado = 0;
        $observacion = '';
        $nit = null;
        $inmueble = null;
        $inmuebleNit = null;
        $conceptoFacturacion = null;

        // Caso especial: sin inmueble ni cédula pero con valor
        if (!$row['inmueble'] && !$row['cedula_nit'] && $row['valor']) {
            $conceptoFacturacion = ConceptoFacturacion::where('id', $this->conceptoFacturacionSinIdentificar)->first();
            $nit = Nits::where('id', $this->nitPorDefecto)->first();
            return compact('nit', 'inmueble', 'inmuebleNit', 'conceptoFacturacion', 'estado', 'observacion');
        }

        // Resolver inmueble
        if ($row['inmueble']) {
            $inmueble = Inmueble::with('zona')
                ->where('nombre', (string) $row['inmueble'])
                ->first();

            if ($inmueble) {
                $inmuebleNit = InmuebleNit::with('nit')
                    ->where('id_inmueble', $inmueble->id)
                    ->first();

                if ($inmuebleNit && $inmuebleNit->nit) {
                    $nit = $inmuebleNit->nit;
                } else {
                    $estado = 1;
                    $observacion .= "El inmueble: {$row['inmueble']}, no tiene propietario!<br>";
                }
            } else {
                $estado = 1;
                $observacion .= "El inmueble: {$row['inmueble']}, no fue encontrado!<br>";
            }
        }

        // Resolver nit por número de documento / email
        if ($row['cedula_nit']) {
            $concepto = ConceptoFacturacion::where('codigo', $row['cedula_nit'])->first();
            $nitConcepto = $row['email']
                ? Nits::where('email', $row['email'])->first()
                : Nits::where('id', $this->nitPorDefecto)->first();

            $nitDocumento = Nits::where('numero_documento', $row['cedula_nit'])
                ->whereRaw('LENGTH(numero_documento) = ?', [strlen($row['cedula_nit'])])
                ->first();

            if (!$nitDocumento && $nitConcepto && ($concepto || $this->conceptoFacturacionSinIdentificar)) {
                $conceptoFacturacion = ConceptoFacturacion::where('id', $this->conceptoFacturacionSinIdentificar)->first();
                $conceptoFacturacion = $concepto; // Nota: mantiene comportamiento original (sobrescribe)
                $nit = $nitConcepto;
            } elseif ($concepto && $nitConcepto) {
                $conceptoFacturacion = $concepto;
                $nit = $nitConcepto;
            } else {
                if (!$nitDocumento) {
                    $estado = 1;
                    $observacion .= "El numero de documento: {$row['cedula_nit']}, no fue encontrado!<br>";
                }
                if (!$nit) {
                    $nit = $nitDocumento;
                } elseif ($nitDocumento && $nit->id != $nitDocumento->id) {
                    $estado = 1;
                    $observacion .= "El numero de documento: {$row['cedula_nit']}, no coincide con el propietario!<br>";
                }
            }
        }

        return compact('nit', 'inmueble', 'inmuebleNit', 'conceptoFacturacion', 'estado', 'observacion');
    }

    /* -------------------- MÉTODOS DE EXTRACTO (SALDOS) -------------------- */

    private function obtenerSaldo(int $nitId, string $fechaManual): ?object
    {
        return (new Extracto($nitId, [3,7], null, $fechaManual))->completo()->first();
    }

    private function obtenerSaldoCXC(int $nitId, string $fechaManual): float
    {
        $extracto = (new Extracto($nitId, [4,8], null, $fechaManual))->completo()->first();
        return $extracto ? $extracto->saldo : 0;
    }

    private function obtenerSaldoTotal(int $nitId): ?object
    {
        return (new Extracto($nitId, [3,7]))->completo()->first();
    }

    private function obtenerSaldoCXPTotal(int $nitId): ?object
    {
        return (new Extracto($nitId, [4,8]))->completo()->first();
    }

    private function obtenerSaldoPendienteHasta(int $nitId, string $fechaCorte): ?object
    {
        return (new Extracto($nitId, [3,7], null, $fechaCorte))->completo()->first();
    }

    /* -------------------- MÉTODOS DE DESCUENTO PRONTO PAGO -------------------- */

    private function calcularDescuentoProntoPago(int $nitId, string $fechaManual, float $totalPago, float $extractoCXC): array
    {
        $inicioMes = Carbon::parse($fechaManual)->format('Y-m-01');
        $facturaDescuento = $this->getFacturaMes($nitId, $inicioMes, $fechaManual);
        
        $descuento = ($facturaDescuento && property_exists($facturaDescuento, 'descuento')) ? $facturaDescuento->descuento : 0;

        $extracto = $this->obtenerSaldo($nitId, $fechaManual);
        if (!$extracto) {
            return [0, 0];
        }

        $totalConDescuento = $totalPago + $descuento + $extractoCXC;
        if ($totalConDescuento >= $extracto->saldo) {
            return [$descuento, 0];
        } else {
            $faltante = $extracto->saldo - $totalConDescuento;
            return [$descuento, $faltante];
        }
    }

    private function getFacturaMes(int $id_nit, string $inicioMes, string $fechaManual): ?object
    {
        $fechaManual = Carbon::parse($fechaManual)->format("Y-m-d");
        $facturas = DB::connection('max')->select("SELECT
                FA.id_nit AS id_nit,
                FA.id AS id_factura,
                FD.id AS id_factura_detalle,
                FD.fecha_manual,
                NULL AS has_pronto_pago,
                FD.id_concepto_facturacion,
                FD.id_cuenta_por_cobrar,
                CF.id_cuenta_gasto,
                CF.nombre_concepto,
                CF.porcentaje_pronto_pago,
                CF.pronto_pago_morosos AS pronto_pago_morosos,
                CF.valor_fijo_pronto_pago,
                FD.documento_referencia,
                SUM(FD.valor) AS subtotal,

                -- Calcula si aplica descuento
                CASE
                    WHEN DATEDIFF('{$fechaManual}', '{$inicioMes}') <= CF.dias_pronto_pago THEN 
                        CASE 
                            WHEN CF.valor_fijo_pronto_pago IS NOT NULL AND CF.valor_fijo_pronto_pago > 0 
                                THEN CF.valor_fijo_pronto_pago
                            ELSE ROUND(SUM(FD.valor) * (CF.porcentaje_pronto_pago / 100), 0)
                        END
                    ELSE 0
                END AS descuento,

                -- Calcula valor total
                CASE
                    WHEN DATEDIFF('{$fechaManual}', '{$inicioMes}') <= CF.dias_pronto_pago THEN 
                        SUM(FD.valor) - (
                            CASE 
                                WHEN CF.valor_fijo_pronto_pago IS NOT NULL AND CF.valor_fijo_pronto_pago > 0 
                                    THEN CF.valor_fijo_pronto_pago
                                ELSE (SUM(FD.valor) * (CF.porcentaje_pronto_pago / 100))
                            END
                        )
                    ELSE SUM(FD.valor)
                END AS valor_total
                
            FROM
                facturacion_detalles FD
                
            LEFT JOIN facturacions FA ON FD.id_factura = FA.id
            LEFT JOIN concepto_facturacions CF ON FD.id_concepto_facturacion = CF.id

            WHERE FD.id_nit = $id_nit
                AND FD.fecha_manual = '{$inicioMes}'
                AND FD.naturaleza_opuesta = 0
                
            GROUP BY FD.id_cuenta_por_cobrar
        ");

        $facturas = collect($facturas);
        if (!count($facturas)) return null;

        $data = (object)[
            'id_factura' => $facturas[0]->id_factura,
            'has_pronto_pago' => $facturas[0]->has_pronto_pago,
            'subtotal' => 0,
            'descuento' => 0,
            'valor_total' => 0,
            'detalle' => []
        ];

        foreach ($facturas as $factura) {
            $fechaFormateada = date('Y-m', strtotime($factura->fecha_manual));
            $tieneProntoPago = $this->tieneProntoPago($id_nit, $factura->id_cuenta_gasto, $fechaFormateada);

            if ($tieneProntoPago) {
                $factura->descuento = 0;
            }

            $data->subtotal += $factura->subtotal;
            $data->descuento += $factura->descuento;
            $data->valor_total += $factura->valor_total;
            $data->detalle[$factura->id_cuenta_por_cobrar] = $factura;
        }

        if ($this->redondeoProntoPago) {
            $data->descuento = $this->roundNumber($data->descuento, $this->redondeoProntoPago);
        }

        return $data;
    }

    private function tieneProntoPago(int $id_nit, int $id_cuenta_gasto, string $fechaManual): bool
    {
        return DocumentosGeneral::where('id_nit', $id_nit)
            ->where('id_cuenta', $id_cuenta_gasto)
            ->where('fecha_manual', 'LIKE', $fechaManual . '%')
            ->exists();
    }

    /* -------------------- MÉTODOS DE VALIDACIÓN Y UTILIDADES -------------------- */

    private function existeRegistro($id_nit = null, $fecha = null, $valor = null, $fecha_limite = null)
    {
        $fecha = $fecha ? Carbon::parse($fecha)->format('Y-m-d') : null;
        $id_comprobante_recibos_caja = $this->idComprobanteRecibosCaja ?? 1;

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
            ->when($id_nit, fn($q) => $q->where('N.id', $id_nit))
            ->when($fecha, fn($q) => $q->where('DG.fecha_manual', $fecha))
            ->when($id_comprobante_recibos_caja, fn($q) => $q->where('DG.id_comprobante', $id_comprobante_recibos_caja))
            ->when($valor, fn($q) => $q->where(fn($sub) => $sub->where('DG.credito', $valor)->orWhere('DG.debito', $valor)))
            ->when($fecha_limite, fn($q) => $q->where('DG.created_at', '<=', $fecha_limite))
            ->first();
    }

    private function parseFecha($fecha, $hora = null): ?string
    {
        $fechaObj = null;
        if ($fecha && str_contains($fecha, '/')) {
            $fechaObj = Carbon::parse($fecha);
        } elseif ($fecha && str_contains($fecha, '-')) {
            $fechaObj = Carbon::parse($fecha);
        } elseif (is_numeric($fecha)) {
            $fechaObj = Carbon::instance(Date::excelToDateTimeObject($fecha));
        }
        if (!$fechaObj) return null;

        $fechaFormateada = $fechaObj->format('Y-m-d');
        if (isset($hora)) {
            try {
                if (is_numeric($hora)) {
                    $horaObj = Carbon::instance(Date::excelToDateTimeObject($hora));
                } else {
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

    private function roundNumber($number, $redondeo = null): float
    {
        if ($redondeo == 0) {
            return (float) round($number);
        } elseif ($redondeo > 0) {
            return round($number / $redondeo) * $redondeo;
        } else {
            return $number;
        }
    }

    private function isValidRow($row): bool
    {
        // 1. Si todos los campos clave están vacíos → ignorar
        if (
            empty($row['cedula_nit']) &&
            empty($row['fecha_manual']) &&
            empty($row['valor'])
        ) {
            return false;
        }

        // 2. Si falta alguno obligatorio → inválida
        if (
            empty($row['cedula_nit']) ||
            empty($row['fecha_manual']) ||
            empty($row['valor'])
        ) {
            return false;
        }

        // 3. Evitar fórmulas tipo "=IF(...)"
        if (is_string($row['valor']) && str_starts_with($row['valor'], '=')) {
            return false;
        }

        return true;
    }

    /* -------------------- MÉTODOS REQUERIDOS POR LAS INTERFACES -------------------- */

    public function prepareForValidation($data, $index)
    {
        $fileHeaders = array_keys($data);
        $requiredHeaders = ['inmueble', 'cedula_nit', 'fecha_manual', 'valor', 'email'];
        
        if (!$this->isValidRow($data)) {
            return [];
        }

        if (array_diff($requiredHeaders, $fileHeaders)) {
            throw ValidationException::withMessages([
                'headers' => ['El archivo no tiene las cabeceras correctas: ' . implode(', ', $requiredHeaders)]
            ]);
        }

        return $data;
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function rules(): array
    {
        return [
            '*.inmueble'   => 'nullable',
            '*.cedula_nit' => 'nullable',
            '*.fecha_manual' => 'nullable',
            '*.valor'      => 'nullable',
            '*.email'      => 'nullable',
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}