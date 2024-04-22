<?php

namespace App\Http\Controllers\Traits;

use DB;
use App\Models\Portafolio\Comprobantes;
use App\Models\Portafolio\DocumentosGeneral;


trait BegConsecutiveTrait
{
    /**
     * @param mixed $model
     * @param Comprobantes|int $comprobante
     * @param string $fecha
     *
     * @return int|null
     */
    public function getNextConsecutive($comprobante, string $fecha)
    {
		if (is_numeric($comprobante) > 0) {
			$comprobante = Comprobantes::find($comprobante);
		}

		if (!($comprobante instanceof Comprobantes)) {
			return null;
        }

        if (!$comprobante) {
			return null;
        }

        if ($comprobante->tipo_consecutivo == Comprobantes::CONSECUTIVO_MENSUAL) {
            return  $this->getLastConsecutive($comprobante->id, $fecha) + 1;
        }

		return $comprobante->consecutivo_siguiente;
    }

	static function getLastConsecutive($id_comprobante, $fecha)
	{
		$castConsecutivo = 'MAX(CAST(consecutivo AS SIGNED)) AS consecutivo';
		$lastConsecutivo = DocumentosGeneral::select(DB::raw($castConsecutivo))
			->where('id_comprobante', $id_comprobante)
			->where('fecha_manual', 'like', substr($fecha, 0, 7) . '%')
			->first();

		return $lastConsecutivo ? $lastConsecutivo->consecutivo : 0;
	}

    /**
     * @param Comprobantes|int $comprobante
     * @param int $consecutivoActual
     *
     * @return Comprobantes|bool
     */
    public function updateConsecutivo($comprobante, int $consecutivoActual)
    {
        if (is_numeric($comprobante)) {
            $comprobante = Comprobantes::find($comprobante);
        } else if (!($comprobante instanceof Comprobantes)) {
            return false;
        }

		if($comprobante->tipo_consecutivo == Comprobantes::CONSECUTIVO_MENSUAL) {
			$comprobante->consecutivo_siguiente = $consecutivoActual;
		}

		if ($consecutivoActual > $comprobante->consecutivo_siguiente) {
			$comprobante->consecutivo_siguiente = $consecutivoActual;
		}

		$comprobante->consecutivo_siguiente = $comprobante->consecutivo_siguiente + 1;
		$comprobante::unsetEventDispatcher();
		$comprobante->save();

        $comprobante->resolucion()->update(["consecutivo" => $comprobante->consecutivo_siguiente]);

        return $comprobante;
    }
}
