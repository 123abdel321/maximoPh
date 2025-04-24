<?php

namespace App\Helpers\PlacetoPay;

//MODELS
use App\Models\Portafolio\ConRecibos;

class PaymentStatus extends AbstractPlacetoPaySender
{
	private $method = 'POST';
	private $endpoint = '/api/session';

	private $request_id;

	public function __construct($request_id)
	{
		$this->request_id = $request_id;
	}

	public function getMethod(): string
	{
		return $this->method;
	}

	public function getEndpoint(): string
	{
		return $this->endpoint.'/'.$this->request_id;
	}	

	public function getParams(): array
	{   
        return [];
	}
}
