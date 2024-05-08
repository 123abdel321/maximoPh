<?php

namespace App\Helpers\PortafolioERP;

//MODELS
use App\Models\User;
use App\Models\Empresa\Empresa;

class InstaladorEmpresa extends AbstractPortafolioSender
{
	private $method = 'POST';
	private $endpoint = '/register-api-token';

	private $dataUsuario;
	private $dataEmpresa;

	public function __construct(Empresa $dataEmpresa, User $dataUsuario)
	{
		$this->dataEmpresa = $dataEmpresa;
		$this->dataUsuario = $dataUsuario;
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
            'tipo_documento' => 1,
            'numero_documento' => $this->dataEmpresa->nit,
            'razon_social' => $this->dataEmpresa->razon_social,
            'nombres' => $this->dataEmpresa->razon_social,
            'telefono' => $this->dataEmpresa->telefono,
            'direccion' => $this->dataUsuario->address,
            'correo' => $this->dataUsuario->email,
            'password' => $this->dataEmpresa->nit,
		];
	}

}
