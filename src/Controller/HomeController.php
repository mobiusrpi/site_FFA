<?php

namespace App\Controller;

use Dompdf\Dompdf;
use App\Service\PdfService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class HomeController extends AbstractController
{
    #[Route(path: '/', name:'home', methods:['GET'])]
    public function home(): Response
    {
        return $this->render('pages/home.html.twig');
    }
}