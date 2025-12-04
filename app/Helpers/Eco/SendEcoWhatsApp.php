<?php

namespace App\Helpers\Eco;

class SendEcoWhatsApp extends AbstractEcoSender
{
    protected $url = "whatsapp/send";
    
    private $phone;
    private $plantilla_id;
    private $contexto;
    private $parameters;
    private $filterData;

    
    public function __construct(
        String $phone,
        Array $parameters = [],
        Array $filterData = [],
        String $plantilla_id,
        String $contexto
    )
    {
        $this->phone = $phone;
        $this->parameters = $parameters;
        $this->filterData = $filterData;
        $this->plantilla_id = $plantilla_id;
        $this->contexto = $contexto;
    }

    public function getEndpoint(): string
    {
        return $this->url;
    }

    public function getMethod(): string
    {
        return 'POST';
    }

    public function getParams(): array
    {
        return [
            "phone" => $this->phone,
            "plantilla_id" => $this->plantilla_id,
            "contexto" => $this->contexto,
            "filter_metadata" => $this->filterData,
            "parameters" => $this->parameters,
        ];
    }
}