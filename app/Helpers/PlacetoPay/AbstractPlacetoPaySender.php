<?php

namespace App\Helpers\PlacetoPay;

use Illuminate\Support\Facades\Http;
//MODELS
use App\Models\Sistema\Entorno;
use App\Models\Empresa\Empresa;


abstract class AbstractPlacetoPaySender
{
    public abstract function getEndpoint(): string;
    public abstract function getMethod(): string;
    public abstract function getParams(): array;

    public function send()
    {
        $url = $this->getUrl();
        $method = $this->getMethod();
        $dataSender = $this->getDataSender();
        
        $dataResponse = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest'
            ])
            ->timeout(60);

        switch ($method) {//TIPOS DE METODOS
            case 'GET':
                $dataResponse = $dataResponse->get($url, $dataSender);
                break;
            case 'POST':
                $dataResponse = $dataResponse->post($url, $dataSender);
                break;
            case 'PUT':
                $dataResponse = $dataResponse->put($url, $dataSender);
                break;
            case 'DELETE':
                $dataResponse = $dataResponse->delete($url, $dataSender);
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
        $placetopay_url = Entorno::where('nombre', 'placetopay_url')->first();
        $placetopay_url = $placetopay_url ? $placetopay_url->valor : '';

        return $placetopay_url.$this->getEndpoint();
	}

    private function getDataSender()
    {
        [$placetopayLogin, $placetopayTrankey] = $this->getAuthApi();
        
        $seed = date('c');
        $rawNonce = rand();
        $nonce = base64_encode($rawNonce);
        
        $tranKey = base64_encode(hash('sha256', $rawNonce.$seed.$placetopayTrankey, true));
        
        //DATA AUTH
        $authData = [
            'locale' => 'es_CO',
            'auth' => [
                'login' => $placetopayLogin,
                'tranKey' => $tranKey,
                'nonce' => $nonce,
                'seed' => $seed,
            ]
        ];
        //DATA REQUEST
        return array_merge($authData, $this->getParams());
    }

    private function getAuthApi(): array
	{
		$entorno = Entorno::whereIn('nombre', ['placetopay_login', 'placetopay_trankey'])->get();

		$placetopayLogin = '';
		$placetopayTrankey = '';

		if (count($entorno)) {
			$placetopayLogin = $entorno->firstWhere('nombre', 'placetopay_login');
			$placetopayLogin = $placetopayLogin ? $placetopayLogin->valor : '';

			$placetopayTrankey = $entorno->firstWhere('nombre', 'placetopay_trankey');
			$placetopayTrankey = $placetopayTrankey && $placetopayTrankey->valor ? $placetopayTrankey->valor : '';
		}

		return [$placetopayLogin, $placetopayTrankey];
	}
}
