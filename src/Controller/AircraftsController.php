<?php

namespace App\Controller;

use App\Entity\Aircrafts;
use App\Form\AircraftsType;
use App\Repository\AircraftsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class AircraftsController extends AbstractController
{
    #[Route('/aircrafts', name: 'user_aircrafts')]
    public function listAircraft(Request $request, EntityManagerInterface $entityManager, AircraftsRepository $aircraftsRepository, Security $security)
    {
        $user = $security->getUser();
        $aircrafts = $aircraftsRepository->findBy(['user' => $user]); // Get the user's aircrafts
        // Select the first aircraft if available, otherwise set to null
        $selectedAircraft = !empty($aircrafts) ? $aircrafts[0] : null;
        $form = null;       

        // Check if an aircraft_id is provided in the query parameters
        if ($request->query->get('aircraft_id')) {
            $aircraftId = $request->query->get('aircraft_id');
            $selectedAircraft = $aircraftsRepository->findOneBy(['id' => $aircraftId, 'user' => $user]);
        }
        if ($selectedAircraft) {
            $form = $this->createForm(AircraftsType::class, $selectedAircraft);
            $form->handleRequest($request);

            // If the form is submitted and valid, save changes
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();
                $this->addFlash('success', 'Avions mis à jour !');
                return $this->redirectToRoute('home');
            }
        }

        // Pass data to the template: list of aircrafts, selected aircraft (if any), and form (if created)
        return $this->render('registration/aircrafts.html.twig', [
            'aircrafts' => $aircrafts,
            'form' => $form ? $form->createView() : null, // Only pass the form if it exists
            'selectedAircraftId' => $selectedAircraft ? $selectedAircraft->getId() : null, // Set the ID of the selected aircraft if any
        ]);
    }
    #[Route('/aircrafts', name: 'edit_aircraft')]
    public function editAircraft(Request $request, EntityManagerInterface $entityManager, AircraftsRepository $aircraftsRepository, Security $security)
    {
    }

    #[Route('/aircrafts', name: 'delete_aircraft')]
    public function deleteAircraft($id, AircraftsRepository $aircraftsRepository, EntityManagerInterface $entityManager, Security $security)
    {
        $user = $security->getUser(); // Récupérer l'utilisateur connecté
        $aircraft = $aircraftsRepository->findOneBy(['id' => $id, 'user' => $user]); // Chercher l'avion pour l'utilisateur connecté

        if (!$aircraft) {
            $this->addFlash('error', 'Avion non trouvé ou vous n\'avez pas les droits pour le supprimer.');
            return $this->redirectToRoute('user_aircrafts');
        }

        // Supprimer l'avion
        $entityManager->remove($aircraft);
        $entityManager->flush();

        $this->addFlash('success', 'L\'avion a été supprimé avec succès.');

        // Rediriger vers la liste des avions après la suppression
        return $this->redirectToRoute('user_aircrafts');
    }
}