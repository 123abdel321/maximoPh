<?php
namespace App\Helpers;

use App\Http\Controllers\Traits\BegConsecutiveTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException; // Excepción específica para manejar duplicidad
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Exception;
use stdClass;
use DB;

// MODELS (Asegúrate que estos namespaces sean correctos)
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\Comprobantes;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Sistema\DocumentosGeneral;


class Documento
{
    use BegConsecutiveTrait; 

    // --- PROPIEDADES ---

    private $head = [];
    private Collection $rows;
    private $errors = [];
    private ?Model $captura = null;
    private Carbon $created_at; 
    private $shouldUpdateConsecutivo = true;
    private $saveUnbalancedDocuments = true;
    private $conceptoDefault = "SIN OBSERVACIÓN";

    // --- CONSTRUCTOR Y CONFIGURACIÓN ---

    public function __construct(
        ?int $id_comprobante = null, 
        ?Model $captura = null, 
        string $fecha = null, 
        ?int $consecutivo = null, 
        bool $save_unbalanced = true
    )
    {
        $this->rows = new Collection();
        $this->setCreatedAt($fecha ?: date('Y-m-d H:i:s'));
        
        // Si se proporciona un consecutivo o una captura
        $this->shouldUpdateConsecutivo = !$consecutivo && !$captura; 
        
        $this->captura = $captura;
        $this->saveUnbalancedDocuments = $save_unbalanced;
        
        $this->head = [
            "id_comprobante" => $id_comprobante,
            "fecha" => $this->created_at->format('Y-m-d H:i:s'),
            "consecutivo" => $consecutivo ?: $captura?->consecutivo, 
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

    public function setConceptoDefault(string $concepto): self
    {
        if ($concepto !== '') {
            $this->conceptoDefault = $concepto;
        }

        return $this;
    }
    
    public function setCreatedAt(string $fecha) : void
    {
        $this->created_at = Carbon::parse($fecha);
    }
    
    public function getConsecutivo(int $id_comprobante, ?string $fecha = null): ?int
    {
        $fecha = $fecha ?: date('Y-m-d');
        $comprobante = Comprobantes::find($id_comprobante);
        
        if (!$comprobante) {
            return null;
        }

        // Si es consecutivo normal (0)
        if ($comprobante->tipo_consecutivo == Comprobantes::CONSECUTIVO_NORMAL) {
             return $comprobante->consecutivo_siguiente;
        }

        // Si es consecutivo mensual (1)
        return (date('d', strtotime($fecha)) == '01')
            ? 1
            : $comprobante->consecutivo_siguiente;
    }

    // --- MANEJO DE FILAS ---
    
    public function addRow($row, ?int $naturaleza = null): self
    {
        $row = $this->normalize($row);
        $row = $this->completeRowFields($row, $naturaleza);

        if ($row->credito || $row->debito) {
            $this->validateRow($row);
            if (!$this->findAndUpdate($row)) {
                $this->rows->push($row);
            }
        }

        return $this;
    }

    private function normalize($row): DocumentosGeneral
    {
        if (is_array($row)) {
            return new DocumentosGeneral($row); 
        }

        return $row;
    }

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

        $index = $this->rows->search(function($existingRow) use ($searchCriteria) {
            foreach ($searchCriteria as $key => $value) {
                if ($existingRow->{$key} != $value) {
                    return false;
                }
            }
            return true;
        });

        if ($index !== false) {
            $existingRow = $this->rows->get($index);
            $rowUpdated = $this->updateRow($existingRow, $newRow);
            $this->rows->put($index, $rowUpdated);
            return true;
        }

        return false;
    }

    private function updateRow(DocumentosGeneral $existingRow, DocumentosGeneral $newRow): DocumentosGeneral
    {
        $existingRow->debito = round($existingRow->debito + $newRow->debito, 2);
        $existingRow->credito = round($existingRow->credito + $newRow->credito, 2);

        return $existingRow;
    }

    private function validateRow(DocumentosGeneral $row): void
    {
        $errors = [];
        // Cargar la cuenta si no existe
        $cuenta = $row->cuenta ?? PlanCuentas::find($row->id_cuenta);

        if (!$cuenta) {
            $errorMsg = $row->id_cuenta
                ? "El ID de cuenta {$row->id_cuenta} no existe."
                : "El campo id_cuenta es requerido.";
            $this->errors['general'][] = $errorMsg; 
            return;
        }

        if (!$cuenta->auxiliar) {
            $errors["id_cuenta"] = "La cuenta {$cuenta->cuenta} - {$cuenta->nombre} debe ser auxiliar.";
        }
        if ($cuenta->exige_nit && !$row->id_nit) {
            $errors["id_nit"] = "En la cuenta {$cuenta->cuenta} - {$cuenta->nombre}, el campo ID Nit es requerido.";
        }
        if ($cuenta->exige_documento_referencia && !$row->documento_referencia) {
            $errors["documento_referencia"] = "En la cuenta {$cuenta->cuenta} - {$cuenta->nombre}, el campo documento referencia es requerido.";
        }
        if ($cuenta->exige_centro_costos && !$row->id_centro_costos) {
             $errors["id_centro_costos"] = "En la cuenta {$cuenta->cuenta} - {$cuenta->nombre}, el campo id centro costos es requerido.";
        }
        if ($cuenta->exige_concepto && !$row->concepto) {
            $errors["concepto"] = "En la cuenta {$cuenta->cuenta} - {$cuenta->nombre}, el campo concepto es requerido.";
        }
        
        // Validación de Débito y Crédito mutuos
        if ($row->debito === null && $row->credito == 0) {
            $errors["debito"] = "El campo debito es requerido si el campo credito es igual 0.";
        }
        if ($row->credito === null && $row->debito == 0) {
            $errors["credito"] = "El campo credito es requerido si el campo debito es igual 0.";
        }
        if ($row->debito > 0 && $row->credito > 0) {
            $errors["movimiento"] = "Una línea contable no puede tener débito y crédito a la vez.";
        }

        if (!empty($errors)) {
            // Almacenar errores en un formato que identifique la cuenta
            $this->errors['Cuenta ' . $cuenta->cuenta] = $errors; 
        }
    }

    private function completeRowFields(DocumentosGeneral $row, ?int $naturaleza = null): DocumentosGeneral
    {
        // Se usa loadMissing para reducir el problema N+1 si la cuenta ya se cargó en un loop
        $row->loadMissing(['cuenta']); 
        $cuenta = $row->cuenta;
        
        if (!$cuenta) return $row; 

        $naturaleza = $naturaleza ?? $cuenta->naturaleza_cuenta;

        $row->id_cuenta = $row->id_cuenta;
        // Asignación condicional de campos exigidos (si no se exige, es null)
        $row->id_nit = $cuenta->exige_nit ? $row->id_nit : null;
        $row->documento_referencia = $cuenta->exige_documento_referencia ? $row->documento_referencia : null;
        $row->id_centro_costos = $cuenta->exige_centro_costos ? $row->id_centro_costos : null;
        $row->concepto = $cuenta->exige_concepto ? ($row->concepto ?: $this->conceptoDefault) : null;
        
        // Asignación de datos de cabecera (usando los valores de head, que son los que se guardarán)
        $row->fecha_manual = $this->head['fecha']; 
        $row->consecutivo = $this->head['consecutivo'];
        $row->id_comprobante = $this->head['id_comprobante'];
        
        // Asignación de valores D/C y naturaleza
        $row->debito = $naturaleza === PlanCuentas::DEBITO ? round($row->debito, 2) : 0;
        $row->credito = $naturaleza === PlanCuentas::CREDITO ? round($row->credito, 2) : 0;
        $row->saldo = $row->saldo ?: 0;
        $row->naturaleza = $naturaleza;

        return $row;
    }

    // --- GETTERS Y HELPERS ---

    public function get(int $index): array
    {
        $row = $this->rows->get($index); // Usar Collection->get()

        return $row ? $row->toArray() : [];
    }

    public function getRows(): Collection
    {
        return $this->rows;
    }

    public function getHead(): array
    {
        return $this->head;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function filterBy(string $key, $value): Collection
    {
        return $this->rows->filter(function ($r) use ($key, $value) {
             return $r->{$key} == $value;
         });
    }

    public function getBy(array $condiciones, bool $withIndex = false)
    {
        $filtered = $this->rows->filter(function ($row) use ($condiciones) {
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

    public function getTotals(): stdClass
    {
        $totals = new stdClass();
        $totals->debito = $this->rows->sum('debito');
        $totals->credito = $this->rows->sum('credito');
        $totals->diferencia = abs(round($totals->credito - $totals->debito, 2));
        return $totals;
    }

    public function withTotalRow(float $totalFactura, int $naturaleza = PlanCuentas::DEBITO): void
    {
        $totalRow = new stdClass();
        $totalRow->debito = $naturaleza === PlanCuentas::DEBITO ? round($totalFactura) : 0;
        $totalRow->credito = $naturaleza === PlanCuentas::CREDITO ? round($totalFactura) : 0;
        $totalRow->cuenta = (object)['nombre' => "TOTAL FACTURA"]; // Simular cuenta para fines de visualización/debug
        $this->rows->push($totalRow);
    }
    
    public function loadMissing(array $relations)
    {
        foreach ($this->rows as $row) {
            // Solo si es una instancia de Model, se usa loadMissing
            if ($row instanceof Model) {
                $row->loadMissing($relations);
            }
        }
    }

    // --- LÓGICA DE GUARDADO ---
    
    protected function updateRowsWithConsecutive(int $consecutivo): void
    {
        $this->head['consecutivo'] = $consecutivo;
        foreach ($this->rows as $row) {
            $row->consecutivo = $consecutivo;
        }
    }

    protected function isUnbalanced(): bool
    {
        // Se usa una pequeña tolerancia para evitar problemas de coma flotante
        return $this->getTotals()->diferencia > 0.01; 
    }

    protected function validateUnbalancedDocument()
    {
        $totals = $this->getTotals();

        if ($this->isUnbalanced()) {
            $validate = false;

            $capturarDocumentosDescuadrados = VariablesEntorno::where('nombre', 'capturar_documento_descuadrado')->first();
            
            // 1. Validar por Variable de Entorno
            if (!$capturarDocumentosDescuadrados || $capturarDocumentosDescuadrados->valor == 0) {
                $validate = true;
            }
            // 2. Validar por parámetro del constructor
            if (!$this->saveUnbalancedDocuments) {
                $validate = true;
            }
            
            if ($validate) {
                // ... (lógica de formateo de mensaje de error de descuadre) ...
                $debitAccounts = $this->rows->where('debito', '>', 0)->pluck('cuenta.nombre', 'cuenta.cuenta')->toArray();
                $creditAccounts = $this->rows->where('credito', '>', 0)->pluck('cuenta.nombre', 'cuenta.cuenta')->toArray();

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

    protected function validateConsecutiveJump(int $idComprobante, int $currentConsecutivo): bool
    {
        $comprobante = Comprobantes::find($idComprobante);
        
        if (!$comprobante) {
            $this->errors['comprobante'][] = "El comprobante no existe para validar el salto.";
            return false;
        }

        $query = DocumentosGeneral::where('id_comprobante', $idComprobante);
        
        // --- LÓGICA DE FILTRADO POR TIPO DE CONSECUTIVO ---
        
        // 1. Tipo Anual (Si tuvieras)
        // Se asume que la fecha de la cabecera es $this->head['fecha']
        $fechaDocumento = $this->head['fecha']; 
        
        // Si el tipo de consecutivo requiere reinicio (Mensual o Anual), filtramos por la ventana.
        // Asumimos: 0=Continuo/Normal, 1=Mensual. Si existiera 2=Anual, lo manejaríamos.
        if ($comprobante->tipo_consecutivo == Comprobantes::CONSECUTIVO_MENSUAL) {
            
            // Si es Mensual, filtramos por el AÑO y el MES del documento actual
            $query->whereYear('fecha_manual', Carbon::parse($fechaDocumento)->year)
                ->whereMonth('fecha_manual', Carbon::parse($fechaDocumento)->month);

            // Si es el primer día (y por ende el consecutivo es 1), no hay salto que validar.
            if (Carbon::parse($fechaDocumento)->day === 1 && $currentConsecutivo === 1) {
                return true;
            }

        } else {
            // Si el tipo es ANUAL (asumiendo que existe en tu sistema), filtramos por AÑO
            $query->whereYear('fecha_manual', Carbon::parse($fechaDocumento)->year);
            
            // Si es el primer día del año (y por ende el consecutivo es 1), no hay salto.
            if (Carbon::parse($fechaDocumento)->isSameDay(Carbon::parse($fechaDocumento)->startOfYear()) && $currentConsecutivo === 1) {
                return true;
            }
        }
        
        // --- OBTENER ÚLTIMO CONSECUTIVO USADO ---
        
        // Traer el valor más alto del campo 'consecutivo' dentro del filtro (si aplica)
        $ultimoConsecutivoUsado = $query
            // Forzar la conversión a entero para la comparación y ordenamiento
            ->orderByRaw('CAST(consecutivo AS UNSIGNED) DESC') // Usar ORDER BY RAW para ordenar numéricamente
            ->value('consecutivo');// <-- ¡CORREGIDO! Trae el consecutivo, no el ID.

        // Si no hay documentos previos dentro del período (o en general), no hay salto.
        if (is_null($ultimoConsecutivoUsado) || $ultimoConsecutivoUsado == 0) {
            // Si el consecutivo actual es 1 (y no hay usados), está bien.
            if ($currentConsecutivo === 1) {
                return true;
            }
        }
        
        // --- VALIDACIÓN DE SALTO ---
        
        // El consecutivo actual DEBE ser exactamente uno más que el último consecutivo usado.
        if ($currentConsecutivo === ($ultimoConsecutivoUsado + 1)) {
            return true;
        }
        
        // Error: Salto detectado.
        if ($currentConsecutivo > ($ultimoConsecutivoUsado + 1)) {
            $this->errors['consecutivo'][] = sprintf(
                "¡Error de salto de consecutivo! El último consecutivo usado para este comprobante fue %d, pero el consecutivo a guardar es %d. Esto indica que se saltaron %d números.",
                $ultimoConsecutivoUsado,
                $currentConsecutivo,
                $currentConsecutivo - $ultimoConsecutivoUsado - 1
            );
            return false;
        }
        
        // Error: Consecutivo duplicado o menor (Aunque $this->getConsecutivo() debería prevenir esto)
        if ($currentConsecutivo <= $ultimoConsecutivoUsado) {
            $this->errors['consecutivo'][] = sprintf(
                "¡Error! El consecutivo actual %d es menor o igual al último usado %d. Debe ser %d.",
                $currentConsecutivo,
                $ultimoConsecutivoUsado,
                $ultimoConsecutivoUsado + 1
            );
            return false;
        }

        return true; // En teoría, la única forma de llegar aquí es si pasó la validación.
    }

    public function save(): bool
    {
        if ($this->rows->isEmpty()) {
            $this->errors["general"][] = "No hay documentos a guardar";
            return false;
        }
        
        if ($this->captura && $this->isUnbalanced()) {
            $this->validateUnbalancedDocument();
        }

        if ($this->hasErrors()) {
            return false;
        }

        $comprobante = Comprobantes::find($this->head['id_comprobante']);
        if (!$comprobante) {
            $this->errors['comprobante'][] = "El comprobante no existe";
            return false;
        }

        $validarSaltoConsecutivos = VariablesEntorno::where('nombre', 'validar_salto_consecutivos')->first();

        if ($validarSaltoConsecutivos && $validarSaltoConsecutivos->valor) {
            $currentConsecutivo = $this->getConsecutivo($this->head['id_comprobante'], $this->head['fecha']);
            
            // Validar el salto antes de cualquier reintento
            if (!$this->validateConsecutiveJump($this->head['id_comprobante'], $currentConsecutivo)) {
                return false; 
            }
        }
        
        $maxRetries = 3;
        // Bucle de reintento para manejar la concurrencia en la asignación del consecutivo
        for ($i = 0; $i < $maxRetries; $i++) {
            try {
                // Iniciar la transacción para asegurar atomicidad
                DB::beginTransaction(); 

                // 1. Determinar el consecutivo si es un reintento o si se debe generar
                if ($this->shouldUpdateConsecutivo || $i > 0) {
                    $currentConsecutivo = $this->getConsecutivo($this->head['id_comprobante'], $this->head['fecha']);
                    $this->updateRowsWithConsecutive($currentConsecutivo); 
                }
                
                // 2. Guardar Documentos
                if ($this->captura) {
                    $this->saveDocumentsWithCapture();
                } else {
                    $this->saveDocumentsWithoutCapture();
                }

                // 3. Actualizar el consecutivo siguiente en el Comprobante (si es necesario)
                if ($this->shouldUpdateConsecutivo) {
                    $this->updateConsecutivo($this->head['id_comprobante'], $this->head['consecutivo']);
                }

                DB::commit();
                return true;

            } catch (QueryException $e) {
                DB::rollBack();
                
                // Código de error 23000 es común para UNIQUE constraint violation en MySQL.
                if ($e->getCode() == "23000" && $this->shouldUpdateConsecutivo && $i < $maxRetries - 1) { 
                    // Falla por duplicidad. Reintentamos con un nuevo consecutivo en la próxima iteración.
                    continue; 
                }
                
                $this->errors['documento'][] = "Error de base de datos al guardar: " . $e->getMessage();
                return false;
                
            } catch (Exception $e) {
                DB::rollBack();
                $this->errors['documento'][] = "Error general al guardar: " . $e->getMessage();
                return false;
            }
        }
        
        $this->errors['documento'][] = "No se pudo guardar el documento después de {$maxRetries} intentos debido a un problema de concurrencia.";
        return false;
    }
    
    protected function saveDocumentsWithCapture()
    {
        foreach ($this->rows as $row) {
            $row->created_at = $row->created_at ?: $this->created_at;
            unset($row->naturaleza);
            $row->relation()->associate($this->captura); // Se usa la relación 'relation' en DocumentosGeneral
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