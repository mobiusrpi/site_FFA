<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use DateTimeImmutable;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Form\FormBuilderInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersCrudController extends AbstractCrudController
{   
    use Trait\BlockDeleteTrait;

    public function __construct(
        public UserPasswordHasherInterface $userPasswordHasher
    ) {}   
    
    public static function getEntityFqcn(): string
    {
        return Users::class;
    }
    
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des utilisateurs')
            ->setPageTitle('detail', 'Utilisateur')
            ->setPageTitle('edit', 'Modification d\'un utilisateur')
            ->setDefaultSort([
                'lastname' => 'ASC',
                'firstname' => 'ASC',
            ])
        ;
    }

    public function configureActions(Actions $actions): Actions
        {
            $actions
                ->disable( Action::DELETE)
                ->add(Crud::PAGE_INDEX, Action::DETAIL);

            return $actions;
        }

    public function configureFields(string $pageName): iterable
    {   
        $password = TextField::new('password')
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => '(Répéter)'],
                'mapped' => false,
            ])
            ->setRequired($pageName === Crud::PAGE_NEW)
            ->onlyOnForms()
        ;

        $fields = [
            IdField::new('id')->hideWhenCreating()->hideOnIndex()->hideOnForm(),
            TextField::new('lastname','Nom'),
            TextField::new('firstname','Prénom'),
            TextField::new('flyingclub','Aéroclub d\'inscription'),            
            EmailField::new('email','Adresse email'),            
            TextField::new('phone','Téléphone'),            
            TextField::new('licenseFfa','Licence FFA'),            
            DateTimeField::new('dateBirth','Date de naissance')->setFormat('dd MMMM yyyy')->hideOnIndex(),
            ArrayField::new('roles','Rôles')->hideOnIndex(),
            BooleanField::new('isCompetitor','Compétiteurs ?')->hideOnIndex(),
            BooleanField::new('isVerified','Email vérifié ?')->hideOnIndex(),
            DateTimeField::new('createdAt')->onlyOnDetail() ,
            DateTimeField::new('updatedAt')->onlyOnDetail(),
        ];

        $fields[] = $password;

        return $fields;
 
    }

    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);
        return $this->addPasswordEventListener($formBuilder);
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
        return $this->addPasswordEventListener($formBuilder);
    }

    private function addPasswordEventListener(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        return $formBuilder->addEventListener(FormEvents::POST_SUBMIT, $this->hashPassword());
    }

    private function hashPassword() {
        return function($event) {
            $form = $event->getForm();
            if (!$form->isValid()) {
                return;
            }
            $password = $form->get('password')->getData();
            if ($password === null) {
                return;
            }

            $hash = $this->userPasswordHasher->hashPassword($this->getUser(), $password);
            $form->getData()->setPassword($hash);
        };
    }
    
    public  function persistEntity(EntityManagerInterface $em,$entityInstance):void
    {
        if (!$entityInstance instanceof Users) return;
        $entityInstance->setCreatedAt(new \DateTimeImmutable);
        $entityInstance->setUpdatedAt(new \DateTimeImmutable);
        parent::persistEntity($em,$entityInstance);
    }
}
