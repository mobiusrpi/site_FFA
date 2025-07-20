<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TestStartOrderController extends AbstractController
{
    #[Route('/test/start/order', name: 'app_test_start_order')]
    public function index(): Response
    {
        return $this->render('test_start_order/index.html.twig', [
            'controller_name' => 'TestStartOrderController',
        ]);
    }
}
