<?php

namespace DailyCheckinBundle\Event;

use DailyCheckinBundle\Entity\Award;
use DailyCheckinBundle\Entity\Record;
use Symfony\Contracts\EventDispatcher\Event;

class AfterCheckinEvent extends Event
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

    private Record $record;

    private ?Award $award = null;

    public function getRecord(): Record
    {
        return $this->record;
    }

    public function setRecord(Record $record): void
    {
        $this->record = $record;
    }

    public function getAward(): ?Award
    {
        return $this->award;
    }

    public function setAward(?Award $award): void
    {
        $this->award = $award;
    }
}
