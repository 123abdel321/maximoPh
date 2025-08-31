<?php

namespace App\Helpers\WhatsApp;

class SendTwilioWhatsApp extends AbstractTwilioWhatsAppSender
{
    private $to;
    private $contentSid;
    private $parameters;
    private $from;

    public function __construct(String $contentSid, String $to, Array $parameters = [])
    {
        $this->contentSid = $contentSid;
        $this->to = $to;
        $this->parameters = $parameters;
        $this->from = env('TWILIO_WHATSAPP_FROM');
    }

    public function getContentSid(): string
    {
        return $this->contentSid;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getTo(): string
    {
        return "whatsapp:+" . ltrim($this->to, '+');
    }

    public function getFrom(): string
    {
        $from = env('TWILIO_WHATSAPP_FROM');
        return 'whatsapp:' . $from;
        
    }

    public function getMessagingServiceSid(): string
    {
        return $this->messagingServiceSid;
    }
}