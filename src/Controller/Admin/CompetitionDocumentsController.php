<?php

namespace App\Controller\Admin;

use App\Controller\Admin\CompetitionDocumentsCrudController;
use App\Entity\CompetitionDocuments;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CompetitionDocumentsController extends AbstractController
{

    #[Route('/admin/document/{id}/download', name:'admin_document_download')]
    public function download(
        CompetitionDocuments $document
    ): Response
    {
        $path =
            $this->getParameter('kernel.project_dir')
            . '/storage/competitions/'
            . $document->getCompetition()->getId()
            . '/documents/'
            . $document->getStoredFilename();

        if (!file_exists($path)) {
            throw $this->createNotFoundException(
                'Fichier introuvable'
            );
        }

        return $this->file(
            $path,
            $document->getOriginalFilename()
        );
    }

    #[Route(
    '/admin/competition/{competId}/documents',  name: 'admin_competition_documents')]

    public function documents(
        int $competId,
        AdminUrlGenerator $adminUrlGenerator
    ): Response
    {
        return $this->redirect(
            $adminUrlGenerator
                ->setController(CompetitionDocumentsCrudController::class)
                ->setAction(Action::INDEX)
                ->set('competition', $competId)
                ->generateUrl()
        );
    }

}