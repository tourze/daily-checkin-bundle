<?php

namespace DailyCheckinBundle\Controller\Admin;

use DailyCheckinBundle\Entity\Award;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class DailyCheckinAwardCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Award::class;
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
