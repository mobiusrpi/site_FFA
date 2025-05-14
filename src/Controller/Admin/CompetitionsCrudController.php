<?php

namespace App\Controller\Admin;

use App\Entity\Crews;
use App\Entity\Competitions;
use App\Form\RegistrationType;
use App\Form\ManageCompetitionType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetitionsRepository;
use App\Repository\AccommodationsRepository;
use App\Repository\TypeCompetitionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminAction;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CompetitionsCrudController extends AbstractCrudController
{       
    use Trait\BlockDeleteTrait;    

    private $competId;

    public static function getEntityFqcn(): string
    {
        return Competitions::class;
    }

   public function configureFields(string $pageName): iterable
    {
        return [        
            FormField::addColumn(4),
            TextField::new('name'),
            TextField::new('location'),            
            DateField::new('startDate', 'Date de début')->setFormat('dd/MM/yy'),
            DateField::new('endDate', 'Date de fin')->setFormat('dd/MM/yy'),
            BooleanField::new('selectable')
                ->renderAsSwitch(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $registerListAction = Action::new('registerListAction', 'Liste des inscrits')
            ->linkToRoute('admin_registered_crews_list',
                function (Competitions $competition) {
                    return [
                        'competId' =>$competition->getId(),
                    ];
                });

        $newRegistrationAction = Action::new('newRegistrationAction', 'Nouvelle Inscription')
            ->linkToRoute('admin_registration_crew_new',
                function (Competitions $competition) {
                    return [
                        'competId' => $competition->getId(),
                    ];
                });
/*
        $editRegistrationAction = Action::new('editRegistrationAction', 'Nouvelle Inscription')
            ->linkToRoute('admin_registration_crew_edit',
                function (Competitions $competition,Crews $crew) {
                    return [
                        'competId' => $competition->getId(),
                        'crewId' => $crew->getId(),
                    ];
                });
*/
        $manageCompetitionAction = Action::new('manageCompetitionAction', 'Gestion')
            ->setIcon('fa fa-clone')
            ->linkToRoute('admin_competition_manage',                
                function (Competitions $competition) {
                    return [
                        'competId' => $competition->getId(),
                    ];
                });
       
        return $actions
            ->add(Crud::PAGE_INDEX, $registerListAction)
            ->add(Crud::PAGE_INDEX, $newRegistrationAction)
//            ->add(Crud::PAGE_INDEX, $editRegistrationAction)
            ->add(Crud::PAGE_INDEX, $manageCompetitionAction);
    } 

    // Define the route and controller method to handle the custom action
    //The route admin_registered_crews_list is redirected to this function in the file
    //config/routes/easyadmin.yaml

    public function registerListAction( 
        int $competId,
        CompetitionsRepository $repository,  
        Request $request,
        AdminUrlGenerator $adminUrlGenerator
    ): Response
    {
        $registants = $repository->getQueryCrews($competId);
        return $this->render('pages/competitions/registantslist.html.twig', [
            'registants' => $registants,          
        ]);            

    }

    //The route admin_registration_crew_new is redirected to this function in the file
    //config/routes/easyadmin.yaml
    public function newRegistrationAction( 
        int $competId,   
        Request $request,
        CompetitionsRepository $repositoryCompetition,   
        TypeCompetitionRepository $repositoryTypecomp, 
        EntityManagerInterface $em
    ): Response
    {
        $competition = $repositoryCompetition->find($competId);      
        $typecompId = $repositoryTypecomp->findOneBy(['id' =>$competition->getTypeCompetition()->getId()]);
        $competition->setTypecompetition($typecompId);
    
        $crew = new Crews;    
        $crew->setCompetition($competition);             
            
        $formOption = array('compet' => $competition);  
        dd($crew,$formOption);        
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

    //    #[AdminAction(routePath: '/admin/manage', routeName: 'admin_manage_competition', methods: ['GET','POST'])]
    public function manageCompetitionAction(
        int $competId,        
        CompetitionsRepository $repositoryCompetition,
        AccommodationsRepository $repositoryAccommodations,  
        Request $request,
        EntityManagerInterface $entityManager)
    {             
        $competition = $repositoryCompetition->find($competId);           

        $form = $this->createForm(ManageCompetitionType::class, $competition);       

        $form->handleRequest($request);

 //           dd('test');  
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($competition);
            $entityManager->flush();
        }

        return $this->render('admin/manageCompetition.html.twig', [
            'form' => $form->createView(),
            'competition' => $competition,
        ]);
    }
}
