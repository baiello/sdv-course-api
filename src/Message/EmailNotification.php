<?php

namespace App\Message;

use Symfony\Component\Mailer\MailerInterface;

class EmailNotification
{
    public function __construct(
        private string $content
    ) {}

    public function getContent(): string
    {
        return $this->content;
    }
}
