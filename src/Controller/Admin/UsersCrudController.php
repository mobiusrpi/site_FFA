<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use App\Entity\Enum\Gender;
use App\Entity\Enum\CRAList;
use App\Entity\Enum\Polosize;
use Doctrine\ORM\QueryBuilder;
use App\Entity\CompetitionsUsers;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersCrudController extends AbstractCrudController
{   
    private $security;
    private EntityManagerInterface $entityManager;

    use Trait\BlockDeleteTrait;


    public static function getEntityFqcn(): string
    {
        return Users::class;
    }

    public function __construct(
        public UserPasswordHasherInterface $userPasswordHasher,
        Security $security,
        ManagerRegistry $registry
    ) {
        $this->security = $security;
        $this->entityManager = $registry->getManager();
    } 

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setPaginatorPageSize(12)
            ->setDefaultSort(['lastname' => 'ASC'])
            ->setPageTitle('index', 'Liste des utilisateurs')
            ->setPageTitle('detail', 'Utilisateur')
            ->setPageTitle('edit', 'Modification d\'un utilisateur')       
            ->setPageTitle('new', 'Nouvel utilisateur');

    }
   
    public function configureFields(string $pageName): iterable
    {    
        $password = TextField::new('password')
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Mot de passe',
                    'help' => 'Laisez vidide pou ne pas changer',
                ],
                'second_options' => ['label' => '(Répéter)'],
                'mapped' => false,
                
            ])
            ->setRequired($pageName === Crud::PAGE_NEW)
            ->onlyOnForms()
        ;
        if ($pageName === Crud::PAGE_EDIT) {
            $password->setHelp('Laissez vide pour conserver le mot de passe actuel');
}

        $user = $this->security->getUser();

        $availableRoles = [];

        // Only allow assigning roles that are equal or lower in privilege
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $availableRoles = [
                'Administrateur' => 'ROLE_ADMIN',
                'Manager' => 'ROLE_MANAGER',
                'Utilisateur' => 'ROLE_USER',
            ];
        } elseif ($this->security->isGranted('ROLE_MANAGER')) {
            $availableRoles = [
                'Manager' => 'ROLE_MANAGER',
                'Utilisateur' => 'ROLE_USER',
            ];
        } else {
            $availableRoles = [
                'Utilisateur' => 'ROLE_USER',
            ];
        }
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('lastname','Nom'),            
            TextField::new('firstname','Prénom'),
            EmailField::new('email','Email'), 
            $password,      
            BooleanField::new('isCompetitor','Compétiteur')->hideOnIndex(),            
            TextField::new('phone','Téléphone'),            
            TextField::new('licenseFfa','Licence FFA'),            
            ChoiceField::new('roles')
                ->setChoices($availableRoles)
                ->allowMultipleChoices(true)
                ->renderExpanded(true), // or false for a dropdown

            DateField::new('dateBirth','Date de naissance')->hideOnIndex(),       
               
      
            ChoiceField::new('committee','CRA')
                ->setChoices(array_combine(
                    array_map(fn($case) => $case->value, CRAList::cases()),
                    CRAList::cases()
                ))
                ->hideOnIndex(),
            ChoiceField::new('gender','Sexe')
                ->setChoices(array_combine(
                    array_map(fn($case) => $case->value, Gender::cases()),
                    Gender::cases()
                ))
                ->hideOnIndex(),            ChoiceField::new('poloSize','Taille polo')
                ->setChoices(array_combine(
                    array_map(fn($case) => $case->value, Polosize::cases()),
                    Polosize::cases()
                ))
                ->hideOnIndex(),

            TextField::new('flyingclub','Aéroclub')->hideOnIndex(),  
            BooleanField::new('isVerified','Vérifié')->hideOnIndex(),            
      
        ];
   }
 
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
       
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        // Get the current authenticated user
        $user = $this->security->getUser();
        // Check if the user has a specific role and modify the query accordingly
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            // If the user is an admin, show all users
            $qb->orderBy('entity.lastname', 'ASC'); // <-- manually set order 

            return $qb;
        }

        if (in_array('ROLE_MANAGER', $user->getRoles(), true)) {
            // First: Get all user IDs linked via pilot or navigator roles in competitions managed by this user
            $subQb = $this->entityManager->createQueryBuilder()
                ->select('DISTINCT u.id')
                ->from(Users::class, 'u')
                ->leftJoin('u.pilot', 'pilotCrew')
                ->leftJoin('u.navigator', 'navigatorCrew')
                ->leftJoin(CompetitionsUsers::class, 'cu', 'WITH', 'cu.user = :manager')
                ->leftJoin('pilotCrew.competition', 'comp1')
                ->leftJoin('navigatorCrew.competition', 'comp2')
                ->where('cu.competition = comp1 OR cu.competition = comp2')
                ->setParameter('manager', $user);

            $userIds = array_map(fn($row) => $row['id'], $subQb->getQuery()->getArrayResult());

            if (count($userIds) > 0) {
                $qb->andWhere($qb->expr()->in('entity.id', ':userIds'))
                ->setParameter('userIds', $userIds);
            } else {
                $qb->andWhere('1 = 0');
            }

            // Apply the ordering here
            $qb->orderBy('entity.lastname', 'ASC');
        }

        return $qb;
    }
}
