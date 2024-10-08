<?php

namespace App\Helpers\PortafolioERP;

//MODELS
use App\Models\Sistema\Facturacion;

class FacturacionERP extends AbstractPortafolioSender
{
	private $method = 'POST';
	private $endpoint = '/generar-documentos';

	private $id_nit;
	private $periodo_facturar;

	public function __construct($periodo_facturar, $id_nit = null)
	{
		$this->periodo_facturar = $periodo_facturar;
		$this->id_nit = $id_nit;
	}

	public function getMethod(): string
	{
		return $this->method;
	}

	public function getEndpoint(): string
	{
		return $this->endpoint;
	}	

	public function getParams(): array
	{
        $facturas = Facturacion::with('detalle')
            ->where('fecha_manual', $this->periodo_facturar)
			->when($this->id_nit, function ($query) {
				$query->where('id_nit', $this->id_nit);
			})
            ->get();

        $facturasToPortafolio = [];

        foreach ($facturas as $factura) {
            foreach ($factura->detalle as $detalle) {

                $facturasToPortafolio[] = (object)[
					'id_nit' => $detalle->id_nit,
					'id_cuenta_por_cobrar' => $detalle->id_cuenta_por_cobrar,
					'id_cuenta_ingreso' => $detalle->id_cuenta_ingreso,
					'id_comprobante' => $detalle->id_comprobante,
					'id_centro_costos' => $detalle->id_centro_costos,
					'fecha_manual' => $detalle->fecha_manual,
					'documento_referencia' => $detalle->documento_referencia,
					'documento_referencia_anticipo' => $detalle->documento_referencia_anticipo,
					'valor' => $detalle->valor,
					'concepto' => $detalle->concepto,
					'naturaleza_opuesta' => $detalle->naturaleza_opuesta,
					'token_factura' => $factura->token_factura,
				];
            }
        }
		
		return [
            'documento' => $facturasToPortafolio
		];
	}
}
