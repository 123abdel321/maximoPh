<?php

namespace App\Helpers\PortafolioERP;

//MODELS
use App\Models\User;
use App\Models\Empresa\Empresa;

class InstaladorEmpresa extends AbstractPortafolioSender
{
	private $dataUsuario;
	private $dataEmpresa;
	private $endpoint = '/register-api-token';

	public function __construct(Empresa $dataEmpresaEmpresa, User $dataUsuario)
	{
		$this->dataEmpresa = $dataEmpresaEmpresa;
		$this->dataUsuario = $dataUsuario;
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
