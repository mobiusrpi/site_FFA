<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

class EasyAdminPrintCssListener
{
    public function onKernelResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();
        $content = $response->getContent();

        // injecte le CSS juste avant </head>
        $css = '<link rel="stylesheet" href="/css/easyadmin-print.css">';
        $content = str_replace('</head>', $css.'</head>', $content);

        $response->setContent($content);
    }
}
