<?php

namespace DailyCheckinBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum RewardGetType: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case AND = 'and';
    case OR = 'or';

    public function getLabel(): string
    {
        return match ($this) {
            self::AND => '并列',
            self::OR => '或者',
        };
    }
}
