<?php

namespace App\Controller\Admin;

use App\Entity\Results;
use Symfony\Component\Mime\Email;
use App\Repository\ResultsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ResultsCrudController extends AbstractCrudController
{

    public static function getEntityFqcn(): string
    {
        return Results::class;
    }

    //The route admin_result_selected_email is redirected to this function in the file
    //config/routes/easyadmin.yaml

    public function selectedEmail(
        Request $request,
        ResultsRepository $resultsRepository,
        MailerInterface $mailer
    ): RedirectResponse {
        $typeCompetId = $request->request->get('typeCompetId') ?? $request->query->get('typeCompetId');

        return $this->redirectToRoute('admin_results_selection', [
            'typeCompetId' => $typeCompetId,
            'wip' => 1,
        ]);
    
    // todo link with Pipper
    
        // Get selected result IDs from form POST
        $selectedResultIds = $request->request->all('selectedResults');
        if (!is_array($selectedResultIds)) {
            $selectedResultIds = [];
        }
    dd($selectedResultIds);  
        if (empty($selectedResultIds)) {
            $this->addFlash('warning', 'Aucun équipage sélectionné.');
            return $this->redirectToRoute('admin_results_selection',[
                'typeCompetId' => $request->request->get('typeCompetId'),
                'wip' => 1,  // flag to show message
            ]);
        }

        // Fetch results with crews
        $results = $resultsRepository->findBy(['id' => $selectedResultIds]);

        foreach ($results as $result) {
            $crew = $result->getCrew(); // Assuming getCrew() returns Crew entity
            $crewEmail = $crew->getEmail(); // Assuming Crew has getEmail()
            $crewName = $crew->getName();   // Or however you get their name

            // Compose and send email
            $email = (new Email())
                ->from('admin@example.com')   // Your sender address
                ->to($crewEmail)
                ->subject('Notification de Résultats')
                ->text("Bonjour $crewName,\n\nVoici vos résultats...\nClassement: {$result->getRanking()}\nScore: {$result->getScore()}\n\nCordialement.");

            $mailer->send($email);
        }

        $this->addFlash('success', count($results) . ' emails envoyés.');

        // Redirect back to results page or dashboard
        return $this->redirectToRoute('admin_results_selection',[
            'typeCompetId' => $request->request->get('typeCompetId'),
            'wip' => 1,  // flag to show message
        ]);
    }

}
