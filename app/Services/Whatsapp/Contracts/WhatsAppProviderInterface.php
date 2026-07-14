<?php

namespace App\Services\WhatsApp\Contracts;

use App\Services\WhatsApp\DTO\SendMessageResult;

interface WhatsAppProviderInterface
{
    public function sendText(
        string $recipient,
        string $message,
        bool $previewUrl = false
    ): SendMessageResult;

    public function sendTemplate(
        string $recipient,
        string $template,
        string $languageCode = 'en_US',
        array $components = []
    ): SendMessageResult;
}