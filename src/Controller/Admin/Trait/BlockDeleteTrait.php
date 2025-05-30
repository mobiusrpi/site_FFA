<?php

namespace App\Controller\Admin\Trait;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

trait BlockDeleteTrait
{
    public function configureActions(Actions $actions): Actions
    {
        $actions
            ->disable( Action::DELETE);

        return $actions;
    }
}
