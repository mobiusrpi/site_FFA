<?php

namespace App\Controller\Admin;

use App\Entity\Results;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ResultsCrudController extends AbstractCrudController
{

    public static function getEntityFqcn(): string
    {
        return Results::class;
    }
}
