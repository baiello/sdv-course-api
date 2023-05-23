<?php

namespace App\MessageHandler;

use App\Message\EmailNotification;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
class EmailNotificationHandler
{
    public function __construct(
        private  MailerInterface $mailer
    ) {}

    public function __invoke(EmailNotification $emailNotification)
    {
        $email = (new Email())
            ->from('hello@example.com')
            ->to('you@example.com')
            ->subject('Mail de test')
            ->text('Mail de test')
            ->html('<p>'.$emailNotification->getContent().'</p>');

        $this->mailer->send($email);
    }
}
