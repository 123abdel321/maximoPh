<?php

namespace App\Helpers\PortafolioERP;

use Illuminate\Support\Facades\Http;
//MODELS
use App\Models\Empresa\Empresa;

abstract class AbstractPortafolioSender
{
    public abstract function getEndpoint(): string;
    public abstract function getMethod(): string;
    public abstract function getParams(): array;

    public function send($id_empresa = null)
    {
        $empresa = Empresa::find($id_empresa);
        
        $bearerToken = '';
        if ($id_empresa) {
            $empresa = Empresa::find($id_empresa);
            $bearerToken = $empresa->token_api_portafolio;
        }

        $url = $this->getUrl();
        $method = $this->getMethod();
        $params = $this->getParams();

        $dataResponse = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $bearerToken,
            ])
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
        
        return [
			"status" => $dataResponse->status(),
			"response" => $response,
		];
    }

    private function getUrl()
	{
        $url = 'https://app.portafolioerp.com/api';
        // if (env("APP_ENV") == 'prod') {//PRODUCCION
        //     $url = 'https://app.portafolioerp.com/api';
        // } else if (env("APP_ENV") == 'production') {//TESTING
        //     $url = 'https://test.portafolioerp.com/api';
        // } else {//LOCAL
        //     $url = 'http://127.0.0.1:8000/api';
        // }
        return $url . $this->getEndpoint();
	}
}
