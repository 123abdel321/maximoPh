<?php
namespace App\Helpers;

use App\Http\Controllers\Traits\BegConsecutiveTrait;
use App\Models\Portafolio\DocumentosGeneral;
use App\Models\Portafolio\Comprobantes;
use App\Models\Portafolio\PlanCuentas;
use Exception;
use Illuminate\Database\Eloquent\Model;
use stdClass;
use DB;

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
	 * @var string
	 */
	private $conceptoDefault = "SIN OBSERVACIÓN";

    /**
     * @param int|null $id_comprobante
     * @param Model|null $captura
     * @param string|null $fecha
     * @param int|null $consecutivo
     */
    public function __construct(int $id_comprobante = null, Model $captura = null, string $fecha = null, int $consecutivo = null)
    {

		$this->setCreatedAt(date('Y-m-d H:i:s'));
        $fecha = $fecha ?: date('y-m-d');


		$this->shouldUpdateConsecutivo = !$consecutivo;

        $consecutivo = isset($captura->consecutivo) ? $captura->consecutivo : $consecutivo;

        $this->captura = $captura;
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
    public function getConsecutivo(int $id_comprobante, string $fecha = null): int
    {
        $consecutivo = null;
        $fecha = date('Y-m-d');
        $comprobante = Comprobantes::find($id_comprobante);
        if (!$comprobante) {
            return null;
        }

        if ($comprobante->tipo_consecutivo) { // 0: normal - 1: mensual
            $consecutivo = $comprobante->consecutivo_siguiente;
        } else {
            $day = date('d', strtotime($fecha));

            if ($day == '01') {
                $consecutivo = 1;
            } else {
                $consecutivo = $comprobante->consecutivo_siguiente;
            }
        }

        return $consecutivo;
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
    public function addRow($row, int $naturaleza = null): Documento
    {
        $row = $this->normalize($row);
        $rowToAdd = $this->completeRowFields($row, $naturaleza);

        if ($rowToAdd->credito || $rowToAdd->debito) {
            $this->validateRow($rowToAdd);

            if (!$this->findAndUpdate($rowToAdd)) {
                $this->rows[] = $rowToAdd;
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

        if ($cuenta) {
            // if ($cuenta && !$cuenta->auxiliar) {
            //     $errors["id_cuenta"] = "La cuenta $cuenta->cuenta - $cuenta->nombre debe ser auxiliar.";
            // }

			// $errors['luji'] = 'probando errores';

            if ($cuenta->exige_nit && !$row->id_nit) {
                $errors["id_nit"] = "El campo id nit es requerido.";
            }
            if ($cuenta->exige_documento_referencia && !$row->documento_referencia) {
                $errors["documento_referencia"] = "El campo documento referencia es requerido.";
            }
            if ($cuenta->exige_centro_costos && !$row->id_centro_costos) {
                $errors["id_centro_costos"] = "El campo id centro costos es requerido.";
            }
            if ($cuenta->exige_concepto && !$row->concepto) {
                $errors["concepto"] = "El campo concepto es requerido.";
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
        } else {
            if ($row->id_cuenta) {
                $errors["id_cuenta"] = "El id cuenta no existe en la tabla de plan de cuentas.";
            } else {
                $errors["id_cuenta"] = "El campo id cuenta es requerido.";
            }
        }

        if (count($errors)) {
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
    public function getBy(array $condiciones, $withIndex = false)
    {
        $filteredRow = null;
        $index = 0;
        $countCondiciones = array_key_exists("naturaleza", $condiciones) ? count($condiciones) - 1 : count($condiciones);

        foreach ($this->rows as $idx => $row) {
            $coincidences = 0;

            if (array_key_exists("naturaleza", $condiciones)) {
                $sameNaturaleza = $condiciones["naturaleza"] == $row->naturaleza;
            } else {
                $sameNaturaleza = true;
            }

            foreach ($condiciones as $key => $value) {
                if ($sameNaturaleza && (in_array($key, $row->getFillable()) && $value == $row->{$key})) {
                    $coincidences++;
                }
            }

            if ($coincidences == $countCondiciones) {
                $index = $idx;
                $filteredRow = $row;
                break;
            }
        }

        if ($withIndex && $filteredRow) {
            return ["index" => $index, "row" => $filteredRow];
        }

        return $filteredRow;
    }

    /**
     *  Retorna array con total debito, credito y diferencia.
     *
     * @return stdClass
     */
    public function getTotals(): stdClass
    {

        $totals = new stdClass();

        $totals->debito = 0;
        $totals->credito = 0;
        $totals->diferencia = 0;

        foreach ($this->rows as $row) {
            $totals->debito = round($totals->debito + $row->debito, 2);
            $totals->credito = round($totals->credito + $row->credito, 2);
        }

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
        $countRows = count($this->rows);

        if (!$countRows) {
            $this->errors["productos"][] = "No hay documentos a guardar";
            return false;
        }

        if ($this->captura) {
            $isUnbalanced = $this->getTotals()->diferencia > 0;

            // if ($isUnbalanced) {
            //     $this->errors['documento'][] = 'Documento descuadrado';
            // }
        }

        if ($this->hasErrors()) {
            return false;
		}

		if ($this->captura) {
			foreach ($this->rows as $row) {
				$row->created_at = $this->created_at;
				unset($row->naturaleza);
				$row->relation()->associate($this->captura);

				if (!$this->captura->documentos()->save($row)) {
					throw new Exception('Error al guardar documentos.');
				}
			}

			if ($this->shouldUpdateConsecutivo) {
				$this->updateConsecutivo($this->head['id_comprobante'], $this->head['consecutivo']);
			}
		} else if (!$this->captura) {
			foreach ($this->rows as $row) {
				$row->created_at = $this->created_at;

				unset($row->naturaleza);

				if (!$row->save()) {
					throw new Exception('Error al guardar documentos.');
				}
			}
		}

		return true;
	}
}
