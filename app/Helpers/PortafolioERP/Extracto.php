<?php

namespace App\Helpers\PortafolioERP;

//MODELS
use App\Models\User;
use App\Models\Empresa\Empresa;

class Extracto extends AbstractPortafolioSender
{
	private $method = 'GET';
	private $endpoint = '/extracto';

	private $id_nit;
	private $id_cuenta;
	private $id_tipo_cuenta;


	public function __construct($id_nit = null, $id_tipo_cuenta = null, $id_cuenta = null)
	{
		$this->id_nit = $id_nit;
		$this->id_cuenta = $id_cuenta;
		$this->id_tipo_cuenta = $id_tipo_cuenta;
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
            'id_nit' => $this->id_nit,
            'id_cuenta' => $this->id_cuenta,
            'id_tipo_cuenta' => $this->id_tipo_cuenta
		];
	}

}
