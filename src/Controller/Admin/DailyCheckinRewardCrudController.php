<?php

namespace DailyCheckinBundle\Controller\Admin;

use DailyCheckinBundle\Entity\Reward;
use DailyCheckinBundle\Enum\RewardGetType;
use DailyCheckinBundle\Enum\RewardType;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Reward>
 */
#[AdminCrud(routePath: '/daily-checkin/reward', routeName: 'daily_checkin_reward')]
final class DailyCheckinRewardCrudController extends AbstractCrudController
{
    /** @var class-string<Reward> */
    private const ENTITY_CLASS = Reward::class;

    public static function getEntityFqcn(): string
    {
        return self::ENTITY_CLASS;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID'),
            TextField::new('name', '奖品名称'),
            ChoiceField::new('type', '奖品类型')->setChoices(fn () => RewardType::cases()),
            TextField::new('value', '奖项值'),
            IntegerField::new('times', '签到次数'),
            IntegerField::new('quantity', '总数量'),
            IntegerField::new('dayLimit', '每日限制'),
            IntegerField::new('sortNumber', '排序'),
            BooleanField::new('isDefault', '是否兜底'),
            BooleanField::new('canShowPrize', '是否展示'),
            ChoiceField::new('rewardGetType', '获取方式')->setChoices(fn () => RewardGetType::cases()),
            AssociationField::new('activity', '活动'),
            DateTimeField::new('createTime', '创建时间'),
            DateTimeField::new('updateTime', '更新时间'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('type')
            ->add('times')
            ->add('activity')
            ->add('isDefault')
            ->add('canShowPrize')
            ->add('rewardGetType')
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Reward')
            ->setEntityLabelInPlural('Rewards')
            ->setDefaultSort(['sortNumber' => 'ASC', 'times' => 'ASC'])
        ;
    }
}
