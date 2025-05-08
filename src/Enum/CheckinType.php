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
}
