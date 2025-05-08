<?php

namespace DailyCheckinBundle\Controller\Admin;

use DailyCheckinBundle\Entity\Record;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class DailyCheckinRecordCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Record::class;
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
