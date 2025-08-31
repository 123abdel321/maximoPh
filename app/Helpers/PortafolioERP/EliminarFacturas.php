<?php

namespace App\Helpers\PortafolioERP;

//MODELS
use App\Models\Sistema\Facturacion;

class EliminarFacturas extends AbstractPortafolioSender
{
	private $method = 'POST';
	private $endpoint = '/bulk-documentos-delete';

	private $periodo_facturar;

	public function __construct($periodo_facturar)
	{
		$this->periodo_facturar = $periodo_facturar;
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
            ->get();

        $facturasToPortafolio = [];

        foreach ($facturas as $factura) {
            foreach ($factura->detalle as $detalle) {

                $facturasToPortafolio[] = (object)[
					'token' => $factura->token_factura
				];
            }
        }
		
		return [
            'documento' => $facturasToPortafolio
		];
	}
}
