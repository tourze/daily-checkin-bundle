<?php

namespace DailyCheckinBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum CheckinType: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case CONTINUE = 'continue';
    case ACCRUED = 'accrued';

    public function getLabel(): string
    {
        return match ($this) {
            self::CONTINUE => '连续签到',
            self::ACCRUED => '累计签到',
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
