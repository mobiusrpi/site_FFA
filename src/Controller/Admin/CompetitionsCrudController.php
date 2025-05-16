<?php

namespace App\Controller\Admin;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Crews;
use App\Service\PdfService;
use App\Entity\Competitions;
use App\Form\RegistrationType;
use App\Form\ManageCompetitionType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetitionsRepository;
use App\Repository\AccommodationsRepository;
use App\Repository\TypeCompetitionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CompetitionsCrudController extends AbstractCrudController
{       
    use Trait\BlockDeleteTrait;    

    private $competId;

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getEntityFqcn(): string
    {
        return Competitions::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des compétitions')
            ->setPageTitle('detail', 'Compétition')
            ->setPageTitle('edit', 'Modification d\'une compétition')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [        
            FormField::addColumn(4),
            TextField::new('name','Désignation'),
            TextField::new('location','Lieu'),            
            AssociationField::new('typecompetition','Type')
                ->setFormTypeOption('choice_label', function($choice) { 
                    return $choice->getTypecomp();
                }),          
            DateField::new('startRegistration', 'Date de début d\'enrégistrement')->setFormat('dd/MM/yy')->onlyOnForms(), 
            DateField::new('endRegistration', 'Date de fin d\'enrégistrement ')->setFormat('dd/MM/yy')->onlyOnForms(),          
            DateField::new('startDate', 'Date de début')->setFormat('dd/MM/yy'),
            DateField::new('endDate', 'Date de fin')->setFormat('dd/MM/yy'),
            BooleanField::new('selectable','Sélection')
                ->renderAsSwitch(),             
            DateField::new('createdAt')->onlyOnDetail() ,            

           
            TextareaField::new('information')->onlyOnForms(),             
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $registeredListAction = Action::new('registeredListAction', 'Liste des inscrits')
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

        $crewsByCompetitionDownloadAction = Action::new('crewsByCompetitionDownloadAction', 'Imprimer')
            ->setIcon('fa fa-clone')
            ->linkToRoute('admin_crews_by_competition_download',                
                function (Competitions $competition) {
                    return [
                        'competId' => $competition->getId(),
                    ];
                });

        $crewsByCompetitionExportAction = Action::new('crewsByCompetitionExportAction', 'Imprimer')
            ->setIcon('fa fa-clone')
            ->linkToRoute('admin_crews_by_competition-download',                
                function (Competitions $competition) {
                    return [
                        'competId' => $competition->getId(),
                    ];
                });

        return $actions
            ->add(Crud::PAGE_INDEX, $registeredListAction)
            ->add(Crud::PAGE_INDEX, $newRegistrationAction)
//            ->add(Crud::PAGE_INDEX, $editRegistrationAction)
            ->add(Crud::PAGE_INDEX, $manageCompetitionAction)
            ->add(Crud::PAGE_INDEX, $crewsByCompetitionDownloadAction)
            ->add(Crud::PAGE_INDEX, $crewsByCompetitionExportAction)
            ->remove(Crud::PAGE_INDEX, Action::DELETE);
    } 

    // Define the route and controller method to handle the custom action
    //The route admin_registered_crews_list is redirected to this function in the file
    //config/routes/easyadmin.yaml

    public function registeredListAction( 
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

    public function manageCompetitionAction(
        int $competId,        
        CompetitionsRepository $repositoryCompetition,
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

        return $this->render('admin/competitions/manageCompetition.html.twig', [
            'form' => $form->createView(),
            'competition' => $competition,
        ]);
    }

    public function  crewsByCompetitionDownloadAction(
        int $competId,
        CompetitionsRepository $repositoryCompetition,
        PdfService $pdf): Response
    {
        $registants = $repositoryCompetition->getQueryCrews($competId);
   
        $html = $this->render('admin/competitions/printCrews.html.twig',['registants' => $registants]);             
        
        return $pdf->showPdfFile($html);
    }

    public  function persistEntity(EntityManagerInterface $em,$entityInstance):void
    {
        if (!$entityInstance instanceof Competitions) return;
        $entityInstance->setCreatedAt(new \DateTimeImmutable);
        parent::persistEntity($em,$entityInstance);
    }


    public function crewsByCompetitionExportAction( 
        int $competId,
        CompetitionsRepository $repositoryCompetition,  
        Request $request,
        AdminUrlGenerator $adminUrlGenerator
    ): Response
    {
       $registants = $repositoryCompetition->getQueryCrews($competId);
       return 'test';
    }

}
