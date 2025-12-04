<?php

namespace App\Helpers\Eco;

use Illuminate\Support\Facades\Http;
//MODELS
use App\Models\Sistema\Entorno;
use App\Models\Empresa\Empresa;


abstract class AbstractEcoSender
{
    protected $endPoint = 'http://127.0.0.1:8989/api/';
    protected ?string $token = null;

    public abstract function getEndpoint(): string;
    public abstract function getMethod(): string;
    public abstract function getParams(): array;

    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function send()
    {
        $url = $this->getUrl();
        $method = $this->getMethod();
        $params = $this->getParams();

        $dataResponse = Http::withHeaders($this->buildHeaders())
            ->timeout(60);

        switch ($method) {//TIPOS DE METODOS
            case 'GET':
                $dataResponse = $dataResponse->get($url, $params);
                break;
            case 'POST':
                $dataResponse = $dataResponse->post($url, $params);
                break;
            case 'PUT':
                $dataResponse = $dataResponse->put($url, $params);
                break;
            case 'DELETE':
                $dataResponse = $dataResponse->delete($url, $params);
                break;        
        }
        
        $response = (object) $dataResponse->json();
        
        return (object)[
			"status" => $dataResponse->status(),
			"response" => $response,
		];
    }

    private function getUrl()
	{
        return "{$this->endPoint}{$this->getEndpoint()}";
	}

    protected function buildHeaders(): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest'
        ];

        // 🟢 LÓGICA CLAVE: Añadir el token Bearer solo si está presente
        if ($this->token) {
            $headers['Authorization'] = 'Bearer ' . $this->token;
        }

        return $headers;
    }
}
