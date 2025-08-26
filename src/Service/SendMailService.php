<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class SendMailService
{
    private $mailer;    
    private $logger;

    public function __construct(MailerInterface $mailer, LoggerInterface $logger)
    {
        $this->mailer = $mailer;        
        $this->logger = $logger; 
    }

    public function send(
        string $from,      
        string $to,
        string $subject,
        string $template,
        array $context
    ):void
    {
        $email  = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate("emails/$template.html.twig")
            ->context($context);
    dd($email::class);
        $this->logger->info("Envoi email async à {$to}");
        $this->mailer->send($email);
    }
}