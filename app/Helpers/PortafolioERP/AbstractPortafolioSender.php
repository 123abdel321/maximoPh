<?php

namespace App\Helpers\PortafolioERP;

use Illuminate\Support\Facades\Http;

abstract class AbstractPortafolioSender
{
    public abstract function getEndpoint(): string;
    public abstract function getParams(): array;

    public function getUrl()
	{
        $url = null;
        if (env("APP_ENV") == 'prod') {//PRODUCCION
            $url = 'https://app.portafolioerp.com/api';
        } else if (env("APP_ENV") == 'test') {//TESTING
            $url = 'https://test.portafolioerp.com/api';
        } else {//LOCAL
            $url = 'http://127.0.0.1:8000/api';
        }
		return $url . $this->getEndpoint();
	}

    public function send()
    {
        $bearerToken = null;
        $url = $this->getUrl();
        $params = $this->getParams();
        
        $dataResponse = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => 'Bearer ' . $bearerToken,
            ])
            ->timeout(60)
            ->post($url, $params);

        $response = (object) $dataResponse->json();

        return [
			"status" => $dataResponse->status(),
			"response" => $response,
		];
    }
}
