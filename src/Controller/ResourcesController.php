<?php

namespace App\Controller;

use App\Entity\Resources;
use App\Entity\Enum\SoftwareProduct;
use App\Repository\ResourcesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

class ResourcesController extends AbstractController
{
    public function __construct(
        private ResourcesRepository $resourcesRepository
    ) {
    }

    /**
     * Téléchargement de la dernière version du software.
     *
     * Cette URL est publique et ne nécessite aucune authentification.
     */
    #[Route(
        '/resource/software/{product}/download',
        name: 'resource_software_download',
        methods: ['GET']
    )]
    public function downloadLatestSoftware(
        string $product
    ): BinaryFileResponse
    {
        try {
            $softwareProduct = SoftwareProduct::from($product);
        } catch (\ValueError) {
            throw $this->createNotFoundException(
                'Produit inconnu.'
            );
        }
        $resource = $this->resourcesRepository
            ->findLatestSoftware($softwareProduct);

        if (!$resource) {
            throw $this->createNotFoundException(
                'Aucun software disponible.'
            );
        }

        if (!$resource->getFilename()) {
            throw $this->createNotFoundException(
                'Aucun fichier associé au software.'
            );
        }

        $file =
            $this->getParameter('kernel.project_dir')
            . '/var/uploads/resources/'
            . $resource->getFilename();

        if (!is_file($file)) {
            throw $this->createNotFoundException(
                'Le fichier demandé n’existe pas.'
            );
        }

        $downloadName =
            $resource->getOriginalFilename()
            ?: $resource->getFilename();

        $response = new BinaryFileResponse($file);

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $downloadName
        );

        return $response;
    }

    /**
     * Téléchargement d'une ressource particulière.
     *
     * Exemple :
     * /resource/12/download
     */
    #[Route(
        '/resource/{id}/download',
        name: 'resource_download',
        requirements: ['id' => '\d+'],
        methods: ['GET']
    )]
    public function download(Resources $resource): BinaryFileResponse
    {
        /*
         * Vérifie que la ressource est active.
         */
        if (!$resource->isEnabled()) {
            throw $this->createNotFoundException();
        }

        /*
         * Vérifie qu'il existe bien un fichier.
         */
        if (!$resource->getFilename()) {
            throw $this->createNotFoundException(
                'Aucun fichier associé à cette ressource.'
            );
        }

        /*
         * Chemin physique.
         */
        $file =
            $this->getParameter('kernel.project_dir')
            . '/var/uploads/resources/'
            . $resource->getFilename();

        /*
         * Vérifie que le fichier existe.
         */
        if (!is_file($file)) {
            throw $this->createNotFoundException(
                'Le fichier demandé n’existe pas.'
            );
        }

        /*
         * Réponse de téléchargement.
         */
        $response = new BinaryFileResponse($file);

        /*
         * Utilise le nom original du fichier.
         */
        $downloadName =
            $resource->getOriginalFilename()
            ?: $resource->getFilename();

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $downloadName
        );

        return $response;
    }
}