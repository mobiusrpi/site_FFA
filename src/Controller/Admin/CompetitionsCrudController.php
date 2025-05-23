<?php

namespace App\Controller\Admin;

use App\Entity\Crews;
use App\Service\PdfService;
use App\Entity\Competitions;
use App\Form\RegistrationType;
use App\Form\CompetitionsUsersType;
use App\Form\ManageCompetitionType;
use App\Repository\CrewsRepository;
use App\Entity\CompetitionAccommodation;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetitionsRepository;
use App\Form\Model\AccommodationCollection;
use App\Repository\AccommodationsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use App\Repository\CompetitionAccommodationRepository;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;


class CompetitionsCrudController extends AbstractCrudController
{          
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
            FormField::addColumn(8),
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
            TextareaField::new('paymentInfo','Informations de réglement')->onlyOnForms(),             
            TextareaField::new('information','Informations utiles')->onlyOnForms(), 
            
            FormField::addPanel('Organisateurs'),
            CollectionField::new('competitionsUsers')
                ->setEntryType(CompetitionsUsersType::class)
                ->onlyOnForms()
                ->allowAdd()
                ->allowDelete()
                ->setLabel('Organisateurs de la compétition')
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $registeredListAction = Action::new('registeredListAction', 'Liste des inscrits')
            ->setIcon('fa fa-list')            
            ->linkToRoute('admin_registered_crews_list',            
                function (Competitions $competition) {
                    return [
                        'competId' =>$competition->getId(),
                    ];
                });

        $newRegistrationAction = Action::new('newRegistrationAction', 'Inscription')
            ->setIcon('fa fa-flag')              
            ->linkToRoute('admin_registration_crew_new',
                function (Competitions $competition) {
                    return [
                        'competId' => $competition->getId(),
                    ];
                });

        $manageCompetitionAction = Action::new('manageCompetitionAction', 'Gestion')
            ->setIcon('fa fa-clone')
            ->linkToRoute('admin_competition_manage',                
                function (Competitions $competition) {
                    return [
                        'competId' => $competition->getId(),
                    ];
                });

        $crewsByCompetitionDownloadAction = Action::new('crewsByCompetitionDownloadAction', 'Liste .pdf')
            ->setIcon('fa fa-list')
            ->linkToRoute('admin_crews_by_competition_download',                
                function (Competitions $competition) {
                    return [
                        'competId' => $competition->getId(),
                    ];
                });

        $crewsByCompetitionExportAction = Action::new('crewsByCompetitionExportAction', 'Exporter .csv')
            ->setIcon('fa fa-file-export')
            ->linkToRoute('admin_crews_by_competition_export',                
                function (Competitions $competition) {
                    return [
                        'competId' => $competition->getId(),
                    ];
                });

        return $actions
 
 //           ->remove(Crud::PAGE_INDEX, Action::EDIT)                       
            ->remove(Crud::PAGE_INDEX, Action::DELETE)           
            ->add(Crud::PAGE_INDEX, $registeredListAction)            
            ->add(Crud::PAGE_INDEX, $newRegistrationAction)  
            ->add(Crud::PAGE_INDEX, $manageCompetitionAction)                       
            ->add(Crud::PAGE_INDEX, $crewsByCompetitionDownloadAction)
            ->add(Crud::PAGE_INDEX, $crewsByCompetitionExportAction);

    } 

    // Define the route and controller method to handle the custom action
    //The route admin_registered_crews_list is redirected to this function in the file
    //config/routes/easyadmin.yaml

    public function registeredListAction( 
        int $competId,
        CrewsRepository $repositoryCrew,  
        CompetitionsRepository $repositoryCompetition,   
    ): Response
    {
        $registants = $repositoryCrew->getQueryCrews($competId);
        $competition = $repositoryCompetition->find($competId);      

        return $this->render('admin/competitions/registantslist.html.twig', [
            'registants' => $registants,  
            'competition' => $competition,        
        ]);            
    }

    public function newRegistrationAction(  
        int $competId,   
        Request $request,  
        CompetitionsRepository $repositoryCompetition, 
        EntityManagerInterface $em
    ): Response
    {   
        $crew = new Crews; 
        $competition = $repositoryCompetition->find($competId); 
 
        $form = $this->createForm(RegistrationType::class, $crew);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();
            $this->addFlash('success', 'Inscription mise à jour avec succès.');

            return $this->redirectToRoute('admin_registered_crews_list', [
                'competId' => $crew->getCompetition()->getId()  
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/crews/crewRegistration.html.twig', [
            'form' => $form->createView(), 
        ]);
    }

    public function manageCompetitionAction(
        int $competId,        
        CompetitionAccommodationRepository $repositoryCompetAccom,
        AccommodationsRepository $repositoryAccommodation,
        CompetitionsRepository $repositoryCompetition,   
        Request $request,
        EntityManagerInterface $entityManager)
    {             
        $competition = $repositoryCompetition->find($competId); 
        $accommodations = $repositoryAccommodation->findAll(); 
        $existing = $repositoryCompetAccom->findBy(['competition' => $competition]);

        $existingByRoomId = [];
        foreach ($existing as $record) {
            $existingByRoomId[$record->getAccommodation()->getId()] = $record;
        }
        $finalList = [];
       
        foreach ($accommodations as $room) {
            if (isset($existingByRoomId[$room->getId()])) {
                $finalList[] = $existingByRoomId[$room->getId()];
            } else {
                $new = new CompetitionAccommodation();
                $new->setCompetition($competition);
                $new->setAccommodation($room);
                $finalList[] = $new;
            }
        }
        $formModel = new AccommodationCollection($finalList);
    
        $form = $this->createForm(ManageCompetitionType::class, $formModel);

        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($form->getData());
            $entityManager->flush();

            $this->addFlash(
              'success',
              'Services modifiés avec succès !'
            ); 

            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/competitions/manageCompetition.html.twig', [
            'form' => $form->createView(),
            'compet' => $competition,
        ]);
    }

    public function  crewsByCompetitionDownloadAction(
        int $competId,
        CrewsRepository $repositoryCrew,
        PdfService $pdf): Response
    {
        $registants = $repositoryCrew->getQueryCrews($competId);

        $fileName = $registants[0]->getCompetition()->getName(); 
        $html = $this->render('admin/competitions/printCrews.html.twig',['registants' => $registants]);             
        
        return $pdf->showPdfFile($html,$fileName);
    }

    public  function persistEntity(EntityManagerInterface $em,$entityInstance):void
    {
        if (!$entityInstance instanceof Competitions) return;
        $entityInstance->setCreatedAt(new \DateTimeImmutable);
        parent::persistEntity($em,$entityInstance);
    }

    private function DateFormated(?\DateTimeInterface $date): string {
        return $date ? $date->format('d-m-Y') : '';
    }

    public function crewsByCompetitionExportAction( 
        int $competId,
        CrewsRepository $repositoryCrew,  
        Request $request,
        AdminUrlGenerator $adminUrlGenerator
    ): Response
    {
        $results = $repositoryCrew->getQueryCrews($competId);   
        $competName = $results[0]->getCompetition()->getName();
//dd($results);
        $data = [];           
        foreach ($results as $result) {
            $data[] = [
                'Competition' => $competName,
                'Equipage' => $result->getId(),
                'Categorie' => $result->getCategory()?->value ?? '',   
                'Pilote' => $result->getPilot()->getLastname() . ' ' . $result->getPilot()->getFirstname(),
                'Pilote_Licence_FFA' => $result->getPilot()->getLicenseFfa() ,
                'Pilote_Telephone' => $result->getPilot()->getPhone() ? $result->getPilot()->getPhone() : '',
                'Pilote_Email' => $result->getPilot()->getEmail() ,
                'Pilote_Date_Naissance' => $this->DateFormated($result->getPilot()->getDateBirth()),
                'Pilote_Aeroclub' => $result->getPilot()->getFlyingclub() ? $result->getPilot()->getFlyingclub() : '',
                'Pilote_CRA' => $result->getPilot()->getCommittee()?->value ?? '',                          
                'Pilote_Sexe' => $result->getPilot()->getGender()?->value ?? '',
                'Pilote_taille_polo' => $result->getPilot()->getPoloSize() ?->value ?? '',
                'Navigateur' => $result->getNavigator()->getLastname() . ' ' . $result->getNavigator()->getFirstname(),
                'Navigateur_Licence_FFA' => $result->getNavigator()->getLicenseFfa(),
                'Navigateur_Telephone' => $result->getNavigator()->getPhone() ? $result->getNavigator()->getPhone() : '',
                'Navigateur_Email' => $result->getNavigator()->getEmail() ,
                'Navigateur_Date_Naissance' => $this->DateFormated($result->getNavigator()->getDateBirth()),
                'Navigateur_Aeroclub' => $result->getNavigator()->getFlyingclub() ? $result->getNavigator()->getFlyingclub() : '',
                'Navigateur_CRA' => $result->getNavigator()->getCommittee() ?->value ?? '',                          
                'Pilote_Sexe' => $result->getPilot()->getGender()?->value ?? '',
                'Navigateur_taille_polo' => $result->getNavigator()->getPoloSize() ?->value ?? '',
                'Immatriculation' => $result->getCallsign() ? $result->getCallSign() : '',
                'Vitesse' => $result->getAircraftSpeed() ?->value ?? '', 
                'Type_avion' => $result->getAircraftType() ? $result->getPilotShared() : '',
                'Avion_partage' => $result->isAircraftSharing() ? 'Oui' : 'Non',
                'Pilote_de_partage' => $result->getPilotShared() ? $result->getPilotShared() : '' ,
                'Creation' => $this->DateFormated($result->getRegisteredAt()),
                'Enregistre_par' => $result->getRegisteredBy()->getLastname() . ' ' . $result->getRegisteredBy()->getFirstname(),
            ];
         }
//dd($data);
        $response = new Response();
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="Insciptions_'.$competName.'.csv"');


        // Ouvrir un flux de sortie
        $handle = fopen('php://output', 'w+');

        // Écrire les en-têtes
        fputcsv($handle, array_keys($data[0]),';');

        // Écrire les données
        foreach ($data as $row) {
            fputcsv($handle, $row,';');
        }

        fclose($handle);

        return $response;
    }

}
