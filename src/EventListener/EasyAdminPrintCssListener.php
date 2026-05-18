<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\Response;

class EasyAdminPrintCssListener
{
    public function onKernelResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();

        // 🚨 NE JAMAIS TOUCHER AUX FICHIERS
        if ($response instanceof BinaryFileResponse) {
            return;
        }

        // uniquement HTML
        if (!$response instanceof Response) {
            return;
        }

        $content = $response->getContent();

        if (!is_string($content)) {
            return;
        }

        // sécurité HTML uniquement
        if (str_contains($content, '</head>')) {

            $css = '<link rel="stylesheet" href="/css/easyadmin-print.css">';

            $content = str_replace(
                '</head>',
                $css . '</head>',
                $content
            );

            $response->setContent($content);
        }
    }
}
