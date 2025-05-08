<?php

namespace DailyCheckinBundle\Controller\Admin;

use DailyCheckinBundle\Entity\Reward;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class DailyCheckinRewardCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Reward::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
