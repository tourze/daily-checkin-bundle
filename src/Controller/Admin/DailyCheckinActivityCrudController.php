<?php

namespace DailyCheckinBundle\Controller\Admin;

use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Enum\CheckinType;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Activity>
 */
#[AdminCrud(routePath: '/daily-checkin/activity', routeName: 'daily_checkin_activity')]
final class DailyCheckinActivityCrudController extends AbstractCrudController
{
    /** @var class-string<Activity> */
    private const ENTITY_CLASS = Activity::class;

    public static function getEntityFqcn(): string
    {
        return self::ENTITY_CLASS;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID'),
            TextField::new('title', '标题'),
            BooleanField::new('valid', '是否启用此活动'),
            DateTimeField::new('startTime', '开始时间'),
            DateTimeField::new('endTime', '结束时间'),
            IntegerField::new('times', '签到次数'),
            ChoiceField::new('checkinType', '签到类型')->setChoices(fn () => CheckinType::cases()),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('valid')
            ->add('checkinType')
            ->add('title')
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Activity')
            ->setEntityLabelInPlural('Activities')
            ->setDefaultSort(['id' => 'DESC'])
        ;
    }
}
