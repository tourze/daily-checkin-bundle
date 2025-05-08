<?php

namespace DailyCheckinBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class BeforeOrPrizeReturnEvent extends Event
{
    private array $result = [];

    public function getResult(): array
    {
        return $this->result;
    }

    public function setResult(array $result): void
    {
        $this->result = $result;
    }

    private array $orPrizes = [];

    private array $andPrizes = [];

    public function getOrPrizes(): array
    {
        return $this->orPrizes;
    }

    public function setOrPrizes(array $orPrizes): void
    {
        $this->orPrizes = $orPrizes;
    }

    public function getAndPrizes(): array
    {
        return $this->andPrizes;
    }

    public function setAndPrizes(array $andPrizes): void
    {
        $this->andPrizes = $andPrizes;
    }
}
