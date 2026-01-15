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
        array $attachments = []
    ): void {
        if ($template) {
            // Envoi via Twig template
            $email = (new TemplatedEmail())
                ->from($this->from)
                ->to($to)
                ->subject($subject)
                ->htmlTemplate("emails/$template.html.twig")   // si tu as le HTML
                ->textTemplate("emails/$template.txt.twig")   // version texte
                ->context($context);

        } elseif ($html) {
            // Envoi HTML brut
            $email = (new Email())
                ->from($this->from)
                ->to($to)
                ->subject($subject)
                ->html($html);
        } else {
            throw new \InvalidArgumentException('Vous devez fournir un template Twig ou du HTML.');
        }

        // Ajouter pièces jointes si présentes
        foreach ($attachments as $path => $filename) {
            $email->attachFromPath($path, $filename);
        }

        // Log avant envoi
        $this->logger->info("Envoi email à {$to} avec sujet '{$subject}'");

        // Envoi réel
        $this->mailer->send($email);
    }
}
