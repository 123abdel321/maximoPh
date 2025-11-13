<?php
namespace App\Helpers;

use App\Http\Controllers\Traits\BegConsecutiveTrait;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Exception;
use stdClass;
use DB;
//MODELS
use App\Models\Portafolio\PlanCuentas;
use App\Models\Portafolio\Comprobantes;
use App\Models\Portafolio\DocumentosGeneral;

class Documento
{
	use BegConsecutiveTrait;

    /**
     * Informacion de la cabeza del documento
     *
     * @var array
     */
    private $head = [];

    /**
     * Registros del documento a insertar en la tabla con_documentos_general
     *
     * @var array
     */
    private $rows = [];

    /**
     * Errores registrados
     *
     * @var array
     */
    private $errors = [];

    /**
     * Captura origen de los datos, se utiliza para el polimorfismo
     *
     * @var Model
     */
    private $captura;

	/**
	 * Fecha de creación del documento
	 *
	 * @var string
	 */
	private $created_at;

	/**
	 * Indica si se debe actualizar el consecutivo siguiente en el comprobante
	 *
	 * @var bool
	 */
	private $shouldUpdateConsecutivo = true;

    /**
	 * Indica si se debe validar si el documento no esta balanceado
	 *
	 * @var bool
	 */
	private $saveUnbalancedDocuments = true;

	/**
	 * @var string
	 */
	private $conceptoDefault = "SIN OBSERVACIÓN";

    /**
     * @param int|null $id_comprobante
     * @param Model|null $captura
     * @param string|null $fecha
     * @param int|null $consecutivo
     */
    public function __construct(?int $id_comprobante = null, ?Model $captura = null, ?string $fecha = null, ?int $consecutivo = null, ?bool $save_unbalanced = true)
    {
        $this->setCreatedAt(date('Y-m-d H:i:s'));
        $fecha = $fecha ?: date('Y-m-d H:i:s');
        $this->shouldUpdateConsecutivo = !$consecutivo;
        $consecutivo = $captura?->consecutivo ?? $consecutivo;
        $this->captura = $captura;
        $this->saveUnbalancedDocuments = $save_unbalanced;
        $this->head = [
            "id_comprobante" => $id_comprobante,
            "fecha" => $fecha,
            "consecutivo" => $consecutivo,
        ];
    }

	public function setShouldUpdateConsecutivo(bool $shouldUpdate)
	{
		$this->shouldUpdateConsecutivo = $shouldUpdate;
	}

	public function getConceptoDefault()
	{
		return $this->conceptoDefault;
	}

	public function setConceptoDefault(string $concepto)
	{
		if ($concepto !== '') {
			$this->conceptoDefault = $concepto;
		}

		return $this;
	}

    /**
     * Devuelve el consecutivo siguiente
     *
     * Si el tipo_consecutivo del comprobante es "0: normal" debe retornar "consecutivo_siguiente".
     * Si el tipo_consecutivo del comprobante es "1: mensual" debe retornar "consecutivo_siguiente"
     * o si ya es mes nuevo debe retornar 1.
     *
     * @param int $id_comprobante
     * @param string|null $fecha
     *
     * @return int
     */
    public function getConsecutivo(int $id_comprobante, ?string $fecha = null): ?int
    {
        $fecha = $fecha ?: date('Y-m-d');
        $comprobante = Comprobantes::find($id_comprobante);
        if (!$comprobante) {
            return null;
        }

        if ($comprobante->tipo_consecutivo) { // 0: normal - 1: mensual
            return $comprobante->consecutivo_siguiente;
        }

        return (date('d', strtotime($fecha)) == '01')
            ? 1
            : $comprobante->consecutivo_siguiente;
    }

    /**
     * Agrega un elemento a la lista de rows a guardar en con_documentos_general
     *
     * Recibe un array clave-valor con los valores de la fila y hace validaciones sobre estos y los asigna a la propiedad $rows
     *
     * @param DocumentosGeneral|array $row
     * @param int|null $naturaleza
     *
     * @return Documento
     */
    public function addRow($row, ?int $naturaleza = null): self
    {
        $row = $this->normalize($row);
        $row = $this->completeRowFields($row, $naturaleza);

        if ($row->credito || $row->debito) {
            $this->validateRow($row);
            if (!$this->findAndUpdate($row)) {
                $this->rows[] = $row;
            }
        }

        return $this;
    }

    /**
     * @param DocumentosGeneral|array $row
     *
     * @return DocumentosGeneral
     */
    private function normalize($row): DocumentosGeneral
    {
		if (is_array($row)) {
			return new DocumentosGeneral($row);
        }

		return $row;
    }

    /**
     * Actualiza row existente
     *
     * Busca row con los parámetros ingresados y actualiza los valores de crédito y débito si existe
     *
     * @param DocumentosGeneral $newRow
     *
     * @return bool
     */
    private function findAndUpdate(DocumentosGeneral $newRow): bool
    {
        $searchCriteria = [
            "id_cuenta" => $newRow->id_cuenta,
            "id_nit" => $newRow->id_nit,
            "documento_referencia" => $newRow->documento_referencia,
            "id_centro_costos" => $newRow->id_centro_costos,
            "concepto" => $newRow->concepto,
            "naturaleza" => $newRow->naturaleza,
        ];

        $existingRow = $this->getBy($searchCriteria, true);
        if ($existingRow) {
            $rowUpdated = $this->updateRow($existingRow["row"], $newRow);
            $this->rows[$existingRow["index"]] = $rowUpdated;

            return true;
        }

        return false;
    }

    /**
     * Actualiza los valores de crédito y débito si existe
     *
     * @param DocumentosGeneral $existingRow
     * @param DocumentosGeneral $newRow
     *
     * @return DocumentosGeneral
     */
    private function updateRow(DocumentosGeneral $existingRow, DocumentosGeneral $newRow): DocumentosGeneral
    {
        $existingRow->debito = round($existingRow->debito + $newRow->debito, 2);
        $existingRow->credito = round($existingRow->credito + $newRow->credito, 2);

        return $existingRow;
    }

    /**
     * Recibe un array clave-valor con los valores de la fila y hace validaciones sobre estos y los asigna a la propiedad $rows
     *
     * *    'id_cuenta': campo obligatorio, debe existir, ser auxiliar
     * *    'id_nit': opcional a menos que la cuenta lo exija
     * *    'documento_referencia': opcional a menos que la cuenta lo exija
     * *    'id_centro_costos': opcional a menos que la cuenta lo exija
     * *    'concepto': opcional a menos que la cuenta lo exija
     * *    'debito': obligatorio si credito es 0, si este campo tiene valor, 'credito' debe ser 0.
     * *    'credito': obligatorio si debito es 0, si este campo tiene valor, 'debito' debe ser 0.
     * *    'saldo': opcional, por defecto es 0
     *
     * @param DocumentosGeneral $row
     *
     * @return void
     */
    private function validateRow(DocumentosGeneral $row): void
    {
        $errors = [];
        $cuenta = PlanCuentas::find($row->id_cuenta);

        if (!$cuenta) {
            $errors["id_cuenta"] = $row->id_cuenta
                ? "El id cuenta no existe en la tabla de plan de cuentas."
                : "El campo id cuenta es requerido.";
            $this->errors['cuenta'] = $errors;
            return;
        }

        if ($cuenta && !$cuenta->auxiliar) {
            $errors["id_cuenta"] = "La cuenta $cuenta->cuenta - $cuenta->nombre debe ser auxiliar.";
        }

        if ($cuenta->exige_nit && !$row->id_nit) {
            $errors["id_nit"] = "En la cuenta {$cuenta->cuenta} - {$cuenta->nombre}, El campo id nit es requerido.";
        }
        if ($cuenta->exige_documento_referencia && !$row->documento_referencia) {
            $errors["documento_referencia"] = "En la cuenta {$cuenta->cuenta} - {$cuenta->nombre}, El campo documento referencia es requerido.";
        }
        if ($cuenta->exige_centro_costos && !$row->id_centro_costos) {
            $errors["id_centro_costos"] = "En la cuenta {$cuenta->cuenta} - {$cuenta->nombre}, El campo id centro costos es requerido.";
        }
        if ($cuenta->exige_concepto && !$row->concepto) {
            $errors["concepto"] = "En la cuenta {$cuenta->cuenta} - {$cuenta->nombre}, El campo concepto es requerido.";
        }
        if ($row->debito === null && $row->credito == 0) {
            $errors["debito"] = "El campo debito es requerido si el campo credito es igual 0.";
        }
        if ($row->credito === null && $row->debito == 0) {
            $errors["credito"] = "El campo credito es requerido si el campo debito es igual 0.";
        }
        if ($row->debito > 0 && $row->credito > 0) {
            $errors["credito"] = "El campo débito debe ser 0 si el campo crédito es mayor a 0.";
            $errors["debito"] = "El campo crédito debe ser 0 si el campo débito es mayor a 0.";
        }

        if (!empty($errors)) {
            $this->errors[strtolower($cuenta->nombre)] = $errors;
        }
    }

    /**
     * Devuelve un array con los datos de $row faltantes
     *
     * Para los datos faltantes se agrega valor null
     *
     * @param DocumentosGeneral|array $row
     * @param string $naturaleza
     *
     * @return DocumentosGeneral
     */
    private function completeRowFields(DocumentosGeneral $row, ?int $naturaleza = null): DocumentosGeneral
    {
		$row->loadMissing(['cuenta', 'centro_costos']);

		$cuenta = $row->cuenta;
        
        $naturaleza = !is_null($naturaleza) ? $naturaleza : $cuenta->naturaleza_cuenta;

        $row->id_cuenta = $row->id_cuenta;
        $row->id_nit = $cuenta->exige_nit ? $row->id_nit : null;
        $row->documento_referencia = $cuenta->exige_documento_referencia ? $row->documento_referencia : null;
        $row->id_centro_costos = $cuenta->exige_centro_costos ? $row->id_centro_costos : null;
        $row->concepto = $cuenta->exige_concepto ? ($row->concepto ?: $this->conceptoDefault) : null;
        $row->fecha_manual = $this->head['fecha'];
        $row->consecutivo = $this->head['consecutivo'];
        $row->id_comprobante = $this->head['id_comprobante'];
        $row->debito = $naturaleza === PlanCuentas::DEBITO ? round($row->debito, 2) : 0;
        $row->credito = $naturaleza === PlanCuentas::CREDITO ? round($row->credito, 2) : 0;
        $row->saldo = $row->saldo ?: 0;
        $row->naturaleza = $naturaleza;

        return $row;
    }

    /**
     * Retorna el array dentro de $this->rows segun la posición $index
     *
     * @param int $index
     *
     * @return array
     */
    public function get(int $index): array
    {
        $row = isset($this->rows[$index]) ? $this->rows[$index] : [];

        return $row;
    }

    /**
     * Retorna los datos de la propiedad rows.
     *
     * @return array
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * Retorna el array de la cabeza con los datos de fecha, consecutivo, id_comprobante, etc...
     *
     * @return array
     */
    public function getHead(): array
    {
        return $this->head;
    }

    /**
     * Retorna un array con los errores obtenidos
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Valida si el documento tiene errores
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    /**
     * Retorna array con los elementos dentro de $this->rows que cumplan con $key y $value
     *
     * @param string $key
     * @param mixed $value
     *
     * @return array
     */
    public function filterBy(string $key, $value): array
    {
        $rows = array_filter($this->rows, function ($r) use ($key, $value) {
            return $r->{$key} == $value;
        });

        return $rows;
    }

    /**
     * Retorna el primer elemento que cumpla con las condicines
     *
     * Ejemplo:
     * * $doc->getBy(['id_cuenta'=>101, 'id_nit'=>302, 'documento_referencia'=> '001232']);
     *
     * @param array $condiciones
     * @param bool $withIndex
     *
     * @return array
     */

    public function getBy(array $condiciones, bool $withIndex = false)
    {
        $collection = collect($this->rows);

        $filtered = $collection->filter(function ($row) use ($condiciones) {
            foreach ($condiciones as $key => $value) {
                if (!isset($row->{$key}) || $row->{$key} != $value) {
                    return false;
                }
            }
            return true;
        });

        if ($withIndex && !$filtered->isEmpty()) {
            $index = $this->rows->search($filtered->first());
            return ["index" => $index, "row" => $filtered->first()];
        }

        return $filtered->first();
    }

    /**
     *  Retorna array con total debito, credito y diferencia.
     *
     * @return stdClass
     */
    
    public function getTotals(): stdClass
    {
        $totals = new stdClass();
        $totals->debito = collect($this->rows)->sum('debito');
        $totals->credito = collect($this->rows)->sum('credito');
        $totals->diferencia = abs(round($totals->credito - $totals->debito, 2));
        return $totals;
    }

    public function withTotalRow(float $totalFactura, int $naturaleza = PlanCuentas::DEBITO): void
    {
        $totalRow = $this->getTotals();
        $totalRow->debito = $naturaleza === PlanCuentas::DEBITO ? round($totalFactura) : 0;
        $totalRow->credito = $naturaleza === PlanCuentas::CREDITO ? round($totalFactura) : 0;
        $totalRow->cuenta = new stdClass();
        $totalRow->cuenta->nombre = "TOTAL FACTURA";
        $this->rows[] = $totalRow;
    }

	public function setCreatedAt(string $fecha) : void
	{
		$this->created_at = $fecha;
	}

	public function loadMissing(array $relations)
	{
		foreach ($this->rows as $row) {
			$row->loadMissing($relations);
		}
	}

    /**
     * Guarda los datos que estén la propiedad rows en la tabla con_documentos_general
     *
     * * Si no tiene registro retorna false
     *
     * * Crea la consulta para guardar en las respectivas tablas recorriendo cada uno de los registros.
     *
     * * Si hay errores en $this->errors debe retornar false.
     *
     * * Debe buscar el consecutivo libre siguiente debido a que mientras se hacía
     * el documento el consecutivo pudo haber cambiado
     *
     * * Se debe validar que si dos registros entran al mismo tiempo no queden con
     * el mismo consecutivo, para eso se puede hacer uso de los "unique" desde base de datos
     * y catchear el error para consultar un nuevo consecutivo.
     *
     *
     * @return bool
     */
    public function save()
    {
        // 1. Validación inicial: ¿Hay filas para guardar?
        if (empty($this->rows)) {
            $this->errors["productos"][] = "No hay documentos a guardar";
            return false;
        }

        // 2. Validación de captura y balance
        if ($this->captura && $this->isUnbalanced()) {
            $this->validateUnbalancedDocument();
        }

        // 3. Si hay errores, salir
        if ($this->hasErrors()) {
            return false;
        }
        
        // 4. Validarción de consecutivo
        // $comprobante = Comprobantes::find($this->head['id_comprobante']);
        // if (!$comprobante) {
        //     $this->errors['comprobante'][] = "El comprobante no existe";
        //     return false;
        // }

        // // Validar si el consecutivo ya existe
        // $query = DocumentosGeneral::where('id_comprobante', $this->head['id_comprobante'])
        //     ->where('consecutivo', $this->head['consecutivo']);

        // if ($comprobante->tipo_consecutivo == Comprobantes::CONSECUTIVO_MENSUAL) {
        //     // Para consecutivo mensual, validar dentro del mismo mes
        //     $fecha = $this->head['fecha'];
        //     $query->whereYear('fecha_manual', date('Y', strtotime($fecha)))
        //         ->whereMonth('fecha_manual', date('m', strtotime($fecha)));
        // }

        // $existingDocument = $query->first();

        // if ($existingDocument) {
        //     $this->errors['consecutivo'][] = sprintf(
        //         "El consecutivo %d ya existe para el comprobante %s%s",
        //         $this->head['consecutivo'],
        //         $comprobante->nombre,
        //         $comprobante->tipo_consecutivo == Comprobantes::CONSECUTIVO_MENSUAL 
        //             ? " en el mes " . date('m/Y', strtotime($this->head['fecha']))
        //             : ""
        //     );
        //     return false;
        // }

        // 4. Guardar documentos según el contexto
        try {
            
            if ($this->captura) {
                $this->saveDocumentsWithCapture();
            } else {
                $this->saveDocumentsWithoutCapture();
            }

            // 5. Actualizar consecutivo si es necesario
            if ($this->shouldUpdateConsecutivo) {
                $this->updateConsecutivo($this->head['id_comprobante'], $this->head['consecutivo']);
            }
        } catch (Exception $e) {
            $this->errors['documento'][] = $e->getMessage();
            return false;
        }

        return true;
    }

    protected function isUnbalanced(): bool
    {
        return $this->getTotals()->diferencia > 0;
    }

    // protected function validateUnbalancedDocument()
    // {
    //     if ($this->saveUnbalancedDocuments) {
    //         $capturarDocumentosDescuadrados = VariablesEntorno::where('nombre', 'capturar_documento_descuadrado')->first();
    //         if (!$capturarDocumentosDescuadrados || $capturarDocumentosDescuadrados->valor == 0) {
    //             $this->errors['documento'][] = 'Documento descuadrado';
    //         }
    //     }
    // }

    protected function validateUnbalancedDocument()
    {
        $totals = $this->getTotals();

        if ($totals->diferencia > 0) {
            $validate = true;

            if ($validate) {
                $debitAccounts = collect($this->rows)->where('debito', '>', 0)->pluck('cuenta.nombre', 'cuenta.cuenta')->toArray();
                $creditAccounts = collect($this->rows)->where('credito', '>', 0)->pluck('cuenta.nombre', 'cuenta.cuenta')->toArray();

                $this->errors['Movimiento contable'][] = sprintf(
                    "Movimiento contable descuadrado <br><br />".
                    "<strong>Diferencia:</strong> %.2f (DÉBITO: %.2f vs CRÉDITO: %.2f)<br />" .
                    "<strong>Cuentas con DÉBITO (%d):</strong> %s.<br />" .
                    "<strong>Cuentas con CRÉDITO (%d):</strong> %s.",
                    $totals->diferencia,
                    $totals->debito,
                    $totals->credito,
                    count($debitAccounts),
                    implode(', ', array_map(fn($k, $v) => "$k ($v)", array_keys($debitAccounts), $debitAccounts)),
                    count($creditAccounts),
                    implode(', ', array_map(fn($k, $v) => "$k ($v)", array_keys($creditAccounts), $creditAccounts))
                );
            }
        }
    }

    protected function saveDocumentsWithCapture()
    {
        foreach ($this->rows as $row) {
            $row->created_at = $row->created_at ?: $this->created_at;
            unset($row->naturaleza);
            $row->relation()->associate($this->captura);
            if (!$this->captura->documentos()->save($row)) {
                throw new Exception('Error al guardar documentos.');
            }
        }
    }

    protected function saveDocumentsWithoutCapture()
    {
        foreach ($this->rows as $row) {
            $row->created_at = $row->created_at ?: $this->created_at;
            unset($row->naturaleza);
            if (!$row->save()) {
                throw new Exception('Error al guardar documentos.');
            }
        }
    }
}
