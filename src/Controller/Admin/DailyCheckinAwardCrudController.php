<?php

namespace DailyCheckinBundle\Controller\Admin;

use DailyCheckinBundle\Entity\Award;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

/**
 * @extends AbstractCrudController<Award>
 */
#[AdminCrud(routePath: '/daily-checkin/award', routeName: 'daily_checkin_award')]
final class DailyCheckinAwardCrudController extends AbstractCrudController
{
    /** @var class-string<Award> */
    private const ENTITY_CLASS = Award::class;

    public static function getEntityFqcn(): string
    {
        return self::ENTITY_CLASS;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID'),
            AssociationField::new('user', '用户'),
            AssociationField::new('record', '签到记录'),
            AssociationField::new('reward', '奖品'),
            DateTimeField::new('createTime', '创建时间'),
            DateTimeField::new('updateTime', '更新时间'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('user')
            ->add('record')
            ->add('reward')
            ->add('createTime')
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Award')
            ->setEntityLabelInPlural('Awards')
            ->setDefaultSort(['createTime' => 'DESC'])
        ;
    }
}
