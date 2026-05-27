<?php

namespace App\Services;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerProvider
{
    private MailerInterface $mailer;
    private string $mailFrom;

    public function __construct(MailerInterface $mailer, string $mailFrom)
    {
        $this->mailer = $mailer;
        $this->mailFrom = $mailFrom;
    }

    public function sendEmail($to, $subject, $body): void
    {
        $email = (new Email())
            ->from($this->mailFrom)
            ->to($to)
            ->subject($subject)
            ->html($body);
        $this->mailer->send($email);
    }
}

