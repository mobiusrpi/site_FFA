<?php

namespace App\Controller\Admin;

use App\Entity\Results;
use App\Repository\ResultsRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\Security;

class ResultsCrudController extends AbstractCrudController
{

    public static function getEntityFqcn(): string
    {
        return Results::class;
    }

    public function __construct(
        private Security $security,
    ) { }

    public function configureActions(Actions $actions): Actions
    {
        return $actions        
            ->remove(Crud::PAGE_INDEX, Action::EDIT)  
            ->remove(Crud::PAGE_INDEX, Action::BATCH_DELETE)              
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)            
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)  
        ;
    }

    //The route admin_result_selected_email is redirected to this function in the file
    //config/routes/easyadmin.yaml
    public function selectedEmail(
        Request $request,
        ResultsRepository $resultsRepository,
        MailerInterface $mailer, 
        Security $security 
    ): RedirectResponse {
        
        /** @var Users|null $connected */
        $connected = $security->getUser();
        $userEmail = $connected?->getEmail();
        $replyTo   = $$userEmail();

        $typeCompetId = $request->request->get('typeCompetId') ?? $request->query->get('typeCompetId');

        return $this->redirectToRoute('admin_results_selection', [
            'typeCompetId' => $typeCompetId,
            'wip' => 1,
        ]);
    
    // todo : link with Pipper
    
        // Get selected result IDs from form POST
        $selectedResultIds = $request->request->all('selectedResults');
        if (!is_array($selectedResultIds)) {
            $selectedResultIds = [];
        }
 
        if (empty($selectedResultIds)) {
            $this->addFlash('warning', 'Aucun équipage sélectionné.');
            return $this->redirectToRoute('admin_results_selection',[
                'typeCompetId' => $request->request->get('typeCompetId'),
                'wip' => 1,  // flag to show message
            ]);
        }
        $replyTo = $userEmail;
        // Fetch results with crews
        $results = $resultsRepository->findBy(['id' => $selectedResultIds]);

        foreach ($results as $result) {
            $crew = $result->getCrew(); 
            $crewEmail = $crew->getEmail(); 
            $crewName = $crew->getName();  

            $personalizedMessage = "Bonjour $crewName,\n\nVoici vos résultats...\nClassement: {$result->getRanking()}\nScore: {$result->getScore()}\n\nCordialement.";

            // Compose and send email
            $mailService->sendEmail(
                $crewEmail,
                'Notification de Résultats',
                'results_notification',  // nom du template Twig sans extension
                [
                    'firstname' => $crewName,
                    'competitionName' => $competition->getName(),
                    'ranking' => $result->getRanking(),
                    'score' => $result->getScore(),
                    'subject' => 'Notification de Résultats', 
                ],
                null,
                [],
                $replyTo
            );

        }

        $this->addFlash('success', count($results) . ' emails envoyés.');

        // Redirect back to results page or dashboard
        return $this->redirectToRoute('admin_results_selection',[
            'typeCompetId' => $request->request->get('typeCompetId'),
            'wip' => 1,  // flag to show message
        ]);
    }

}
