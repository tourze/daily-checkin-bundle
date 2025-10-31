<?php

namespace DailyCheckinBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum RewardType: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case COUPON = 'coupon';
    case CREDIT = 'credit';
    case BADGE = 'badge';

    public function getLabel(): string
    {
        return match ($this) {
            self::COUPON => '优惠券',
            self::CREDIT => '积分',
            self::BADGE => '徽章',
        };
    }

    /**
     * 获取所有枚举的选项数组（用于下拉列表等）
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function toSelectItems(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[] = [
                'value' => $case->value,
                'label' => $case->getLabel(),
            ];
        }

        return $result;
    }
}
