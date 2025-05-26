<?php

namespace App\Controller;

use App\Entity\Accommodations;
use App\Form\AccommodationsType;
use App\Repository\AccommodationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/accommodation')]
final class AccommodationController extends AbstractController
{
    #[Route(name: 'app_accommodation_index', methods: ['GET'])]
    public function index(AccommodationsRepository $accommodationsRepository): Response
    {
        return $this->render('accommodation/index.html.twig', [
            'accommodations' => $accommodationsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_accommodation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $accommodation = new Accommodations();
        $form = $this->createForm(AccommodationsType::class, $accommodation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($accommodation);
            $entityManager->flush();

            return $this->redirectToRoute('app_accommodation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('accommodation/new.html.twig', [
            'accommodation' => $accommodation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_accommodation_show', methods: ['GET'])]
    public function show(Accommodations $accommodation): Response
    {
        return $this->render('accommodation/show.html.twig', [
            'accommodation' => $accommodation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_accommodation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Accommodations $accommodation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AccommodationsType::class, $accommodation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_accommodation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('accommodation/edit.html.twig', [
            'accommodation' => $accommodation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_accommodation_delete', methods: ['POST'])]
    public function delete(
        int $id,
        Request $request,
        Accommodations $accommodation, 
        EntityManagerInterface $entityManager,
    ): Response
    { // dd($id)
        if ($this->isCsrfTokenValid('delete'.$accommodation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($accommodation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_accommodation_index', [], Response::HTTP_SEE_OTHER);
    }
}
