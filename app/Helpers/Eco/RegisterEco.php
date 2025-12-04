<?php

namespace App\Helpers\Eco;

class RegisterEco extends AbstractEcoSender
{
    protected $url = "register";
    
    private $parameters;

    public function __construct(Array $parameters = [])
    {
        $this->parameters = $parameters;
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
        return $this->parameters;
    }
}