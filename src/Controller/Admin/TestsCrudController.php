<?php

namespace App\Controller\Admin;

use App\Entity\Tests;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class TestsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tests::class;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Tests) {
            return;
        }

        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }
}
