<?php

namespace App\Controller\Admin;

use App\Entity\Crews;
use App\Entity\Competitions;
use App\Form\RegistrationType;
use App\Repository\CrewsRepository;
use App\Repository\CompetitionsRepository;
use App\Controller\Admin\CrewsCrudController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Controller\Admin\Trait\BlockDeleteTrait;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\HttpFoundation\RequestStack;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CompetitionsCrudController extends AbstractCrudController
{
    use BlockDeleteTrait;

    private $eventId;

    public static function getEntityFqcn(): string    
    {
            return Competitions::class;
    }

//        $this->eventId = $compet->getId();

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('name'),
            TextField::new('location'),
            DateTimeField::new('startDate')->setFormat('dd MMMM yyyy'),            DateTimeField::new('startDate')->setFormat('dd MMMM yyyy'),
            DateTimeField::new('endDate')->setFormat('dd MMMM yyyy'),
            DateTimeField::new('startRegistration')->setFormat('dd MMMM yyyy'),
            DateTimeField::new('endRegistration')->setFormat('dd MMMM yyyy'),
            BooleanField::new('selectable')
                ->renderAsSwitch(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $registerListAction = Action::new('registerListAction', 'Liste des inscrits')
            ->linkToRoute('admin_registered_crews_list',function (Competitions $competition) {
                return [
                    'eventId' =>$competition->getId(),
                ];
            });

        $newRegistrationAction = Action::new('newRegistrationAction', 'Nouvelle Inscription')
            ->linkToRoute('admin_registration_crew_new',
                function (Competitions $competition) {
                    return [
                        'eventId' => $competition->getId(),
                    ];
                }
        );

        $editRegistrationAction = Action::new('editRegistrationAction', 'Nouvelle Inscription')
            ->linkToRoute('admin_registration_crew_edit',
                function (Competitions $competition,Crews $crew) {
                    return [
                        'eventId' => $competition->getId(),
                        'crewId' => $crew->getId(),
                    ];
                }
        );
       
        return $actions
        ->add(Crud::PAGE_INDEX, $registerListAction)
        ->add(Crud::PAGE_INDEX, $newRegistrationAction);
    }

// Define the route and controller method to handle the custom action
//The route admin_registered_crews_list is redirected to this function in the file
//config/routes/easyadmin.yaml
public function registerListAction( 
    int $eventId,
    CompetitionsRepository $repository,  
    Request $request,
    AdminUrlGenerator $adminUrlGenerator
): Response
{
    $registants = $repository->getQueryCrews($eventId);
    return $this->render('pages/competitions/registantslist.html.twig', [
        'registants' => $registants,          
    ]);            

}

//The route admin_registration_crew_new is redirected to this function in the file
//config/routes/easyadmin.yaml
public function newRegistrationAction( 
    int $eventId,   
    Request $request,
    CompetitionsRepository $repositoryCompetition,
    CrewsRepository $repositoryCrew,  
    EntityManagerInterface $em
): Response
{
    $competition = $repositoryCompetition->find($eventId);      
    $crew = new Crews;              
    $formOption = array('compet' => $competition);          
    $form = $this->createForm(RegistrationType::class, $crew, $formOption);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        $em->persist($crew);
        $em->flush();

        return $this->redirectToRoute('admin_registered_crews_list', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('pages/crews/registration.html.twig', [
        'compet' => $competition,
        'form' => $form     
    ]);
}

//The route admin_registration_crew_edit is redirected to this function in the file
//config/routes/easyadmin.yaml
public function editRegistrationAction( 
    int $eventId,     
    int $crewId,   
    Request $request,
    CompetitionsRepository $repositoryCompetition,
    CrewsRepository $repositoryCrew,  
    EntityManagerInterface $em
): Response
{
    $competition = $repositoryCompetition->find($eventId);      
    $crew = $repositoryCrew->find($crewId);                
    $formOption = array('compet' => $competition);          
    $form = $this->createForm(RegistrationType::class, $crew, $formOption);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        $em->persist($crew);
        $em->flush();

        return $this->redirectToRoute('admin_registered_crews_list', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('pages/crews/registration.html.twig', [
        'compet' => $competition,
        'form' => $form     
    ]);
}
}
