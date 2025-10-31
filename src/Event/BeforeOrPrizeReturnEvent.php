<?php

namespace DailyCheckinBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class BeforeOrPrizeReturnEvent extends Event
{
    /**
     * @var array<string, mixed>
     */
    private array $result = [];

    /**
     * @return array<string, mixed>
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * @param array<string, mixed> $result
     */
    public function setResult(array $result): void
    {
        $this->result = $result;
    }

    /**
     * @var array<mixed>
     */
    private array $orPrizes = [];

    /**
     * @var array<mixed>
     */
    private array $andPrizes = [];

    /**
     * @return array<mixed>
     */
    public function getOrPrizes(): array
    {
        return $this->orPrizes;
    }

    /**
     * @param array<mixed> $orPrizes
     */
    public function setOrPrizes(array $orPrizes): void
    {
        $this->orPrizes = $orPrizes;
    }

    /**
     * @return array<mixed>
     */
    public function getAndPrizes(): array
    {
        return $this->andPrizes;
    }

    /**
     * @param array<mixed> $andPrizes
     */
    public function setAndPrizes(array $andPrizes): void
    {
        $this->andPrizes = $andPrizes;
    }
}
