<?php

namespace DailyCheckinBundle\Controller\Admin;

use DailyCheckinBundle\Entity\Record;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Record>
 */
#[AdminCrud(routePath: '/daily-checkin/record', routeName: 'daily_checkin_record')]
final class DailyCheckinRecordCrudController extends AbstractCrudController
{
    /** @var class-string<Record> */
    private const ENTITY_CLASS = Record::class;

    public static function getEntityFqcn(): string
    {
        return self::ENTITY_CLASS;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID'),
            AssociationField::new('user', '用户'),
            AssociationField::new('activity', '活动'),
            DateField::new('checkinDate', '签到日期'),
            IntegerField::new('checkinTimes', '连续签到次数'),
            BooleanField::new('hasAward', '是否有奖'),
            TextField::new('remark', '备注'),
            DateTimeField::new('createTime', '创建时间'),
            DateTimeField::new('updateTime', '更新时间'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('user')
            ->add('activity')
            ->add('checkinDate')
            ->add('hasAward')
            ->add('createTime')
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Record')
            ->setEntityLabelInPlural('Records')
            ->setDefaultSort(['checkinDate' => 'DESC'])
        ;
    }
}
