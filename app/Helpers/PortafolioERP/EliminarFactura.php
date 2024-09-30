<?php

namespace App\Helpers\PortafolioERP;

//MODELS
use App\Models\Sistema\Facturacion;

class EliminarFactura extends AbstractPortafolioSender
{
	private $method = 'POST';
	private $endpoint = '/bulk-documentos-delete';

	private $token_factura;

	public function __construct($token_factura)
	{
		$this->token_factura = $token_factura;
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
		return [
            'documento' => [(object)['token' => $this->token_factura]]
		];
	}
}
