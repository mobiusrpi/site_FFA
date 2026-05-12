<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SendMailService
{
    private MailerInterface $mailer;
    private LoggerInterface $logger;
    private string $from;

    public function __construct(
        MailerInterface $mailer,
        LoggerInterface $logger,
        #[\Symfony\Component\DependencyInjection\Attribute\Autowire('%env(MAILER_FROM)%')]
        string $mailerFrom
    ) {
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->from   = $mailerFrom;
    }

    /**
     * Envoie un email.
     *
     * @param string $to Destinataire
     * @param string $subject Objet du mail
     * @param string|null $template Nom du template Twig dans templates/emails (optionnel)
     * @param array $context Contexte pour Twig (optionnel)
     * @param string|null $html HTML brut si pas de template
     * @param array $attachments Tableau ['chemin' => 'nom_fichier'] des pièces jointes
     */

    public function send(
        string $to,
        string $subject,
        ?string $template = null,
        array $context = [],
        ?string $html = null,
        array $attachments = [],
        ?string $replyTo = null
    ): void {
        if ($template) {
            $email = (new TemplatedEmail())
                ->from($this->from)
                ->to($to)
                ->subject($subject)
                ->htmlTemplate("emails/$template.html.twig")
                ->textTemplate("emails/$template.txt.twig")
                ->context($context);
        } elseif ($html) {
            $email = (new Email())
                ->from($this->from)
                ->to($to)
                ->subject($subject)
                ->html($html);
        } else {
            throw new \InvalidArgumentException('Vous devez fournir un template Twig ou du HTML.');
        }

        // Ajouter replyTo si défini
        if ($replyTo) {
            $email->replyTo($replyTo);
        }

        // Ajouter pièces jointes si présentes
        foreach ($attachments as $path => $filename) {
            $email->attachFromPath($path, $filename);
        }

        $this->logger->info("Envoi email à {$to} avec sujet '{$subject}'");

        $this->mailer->send($email);
    }

    public function sendToMultiple( 
        string|array $to,
        string $subject,
        ?string $template = null,
        array $context = [],
        ?string $html = null,
        array $attachments = [],
        ?string $replyTo = null
    ): void {

        if ($template) {
            $email = (new TemplatedEmail())
                ->from($this->from)
                ->subject($subject)
                ->htmlTemplate("emails/$template.html.twig")
                ->textTemplate("emails/$template.txt.twig")
                ->context($context);

        } elseif ($html) {
            $email = (new Email())
                ->from($this->from)
                ->subject($subject)
                ->html($html);

        } else {
            throw new \InvalidArgumentException(
                'Vous devez fournir un template Twig ou du HTML.'
            );
        }

        // Gestion des destinataires
        if (is_array($to)) {
            $email->to(...$to);
        } else {
            $email->to($to);
        }

        // Reply-To
        if ($replyTo) {
            $email->replyTo($replyTo);
        }

        // Pièces jointes
        foreach ($attachments as $path => $filename) {
            $email->attachFromPath($path, $filename);
        }

        $this->logger->info(
            'Envoi email à '.(is_array($to) ? implode(', ', $to) : $to)
        );

        $this->mailer->send($email);
    }

}
