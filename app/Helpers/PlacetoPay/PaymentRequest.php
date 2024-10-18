<?php

namespace App\Helpers\PlacetoPay;

//MODELS
use App\Models\Portafolio\ConRecibos;

class PaymentRequest extends AbstractPlacetoPaySender
{
	private $method = 'POST';
	private $endpoint = '/api/session';

	private $id_pago;

	public function __construct($id_pago)
	{
		$this->id_pago = $id_pago;
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
        $recibo = ConRecibos::find($this->id_pago);
        $expire = now()->addMinutes(10)->toIso8601String();
		$codere = base64_encode($recibo->id.'_'.$recibo->id_nit.'_'.$recibo->created_by);

		$return = "http://127.0.0.1:8090/close-payment/".$codere;
		$cancel = "http://127.0.0.1:8090/close-payment/".$codere;

        return [
            'payment' => [
                'reference' => $recibo->id,
                'description' => "Pago por Placetopay",
                'amount' => [
                    'currency' => 'COP',
                    'total' => $recibo->total_abono
                ],
            ],
			"expiration" => $expire,
			"returnUrl" => $return,
			"cancelUrl" => $cancel,
			"ipAddress" => "127.0.0.1",
			"userAgent" => "PlacetoPay Sandbox",
        ];
	}
}
