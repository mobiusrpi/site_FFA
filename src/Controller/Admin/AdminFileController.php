<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminFileController extends AbstractController
{
    #[Route('/admin/import-file', name: 'admin_import_file')]
    public function importFile(): Response
    {
        return $this->render('admin/file_import.html.twig');
    }
}