<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use DateTimeImmutable;
use App\Entity\Enum\Gender;
use App\Entity\Enum\CRAList;
use App\Entity\Enum\Polosize;
use Doctrine\ORM\QueryBuilder;
use App\Entity\CompetitionsUsers;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Form\FormBuilderInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
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

     public function configureFields(string $pageName): iterable
    {
       return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('lastname','Nom'),            
            TextField::new('firstname','Prénom'),
            EmailField::new('email','Email'),            
            TextField::new('password','Mot de passe')->hideOnIndex(),               
            TextField::new('phone','Téléphone'),            
            TextField::new('licenseFfa','Licence FFA'),            
            ArrayField::new('roles','Rôles')->hideOnIndex(),             
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
            BooleanField::new('isCompetitor','Compétiteur')->hideOnIndex(),      

        ];

               }
 
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
       
        // Get the current authenticated user
        $user = $this->security->getUser();
        // Check if the user has a specific role and modify the query accordingly
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            // If the user is an admin, show all users
            $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
            return $queryBuilder;
        }

        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

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

        // ✅ Apply the ordering here
        $qb->orderBy('entity.lastname', 'ASC');
    }


        return $qb;
    }
}
