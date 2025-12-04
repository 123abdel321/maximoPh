<?php

namespace App\Helpers\Eco;

// No necesita más imports que el AbstractEcoSender

class SendEcoEmail extends AbstractEcoSender
{
    protected $url = "email/send";
    
    private $email;
    private $subject;
    private $htmlContent;
    private $metadata;
    private $attachments;

    /**
     * @param string $email Correo electrónico del destinatario.
     * @param string $subject El asunto del correo.
     * @param string $htmlContent El contenido HTML ya renderizado.
     * @param array $metadata Metadatos para rastreo.
     * @param array $attachments Array de archivos adjuntos (Base64).
     */
    public function __construct(
        string $email,
        string $subject,
        string $htmlContent,
        array $metadata = [],
        array $filterData = [],
        array $attachments = []
    ) {
        $this->email = $email;
        $this->subject = $subject;
        $this->htmlContent = $htmlContent;
        $this->metadata = $metadata;
        $this->filterData = $filterData;
        $this->attachments = $attachments;
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
        // El campo 'aplicacion' debería ser dinámico (ej. de configuración de Laravel)
        return [
            "aplicacion" => config('app.name', 'Sistema-Facturacion-Default'), 
            "email" => $this->email,
            "asunto" => $this->subject,
            "html" => $this->htmlContent,
            "metadata" => $this->metadata,
            "filter_metadata" => $this->filterData,
            "archivos" => $this->attachments,
        ];
    }
}