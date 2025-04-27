<?php

namespace App\Helpers\PlacetoPay;

//MODELS
use App\Models\Portafolio\ConRecibos;

class PaymentRequest extends AbstractPlacetoPaySender
{
	private $method = 'POST';
	private $endpoint = '/api/session';

	private $ip;
	private $id_pago;
	private $userAgent;
	private $id_empresa;

	public function __construct($id_pago, $id_empresa, $ip, $userAgent)
	{
		$this->ip = $ip;
		$this->id_pago = $id_pago;
		$this->userAgent = $userAgent;
		$this->id_empresa = $id_empresa;
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

		$return = "https://maximoph.co/close-payment/".$codere;
		$cancel = "https://maximoph.co/close-payment/".$codere;

        return [
            'payment' => [
                'reference' => $recibo->id.'-'.$this->id_empresa,
                'description' => "Pago por Placetopay",
                'amount' => [
                    'currency' => 'COP',
                    'total' => $recibo->total_abono
                ],
            ],
			"expiration" => $expire,
			"returnUrl" => $return,
			"cancelUrl" => $cancel,
			"ipAddress" => $this->ip,
			"userAgent" => $this->userAgent,
        ];
	}
}
