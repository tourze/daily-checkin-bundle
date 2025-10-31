<?php

namespace DailyCheckinBundle\Entity;

use DailyCheckinBundle\Repository\AwardRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

/**
 * 这里的award取的是名词的意思，有颁发之类的意思，跟Lottery那边有一点点区别
 *
 * @implements ApiArrayInterface<string, mixed>
 */
#[ORM\Entity(repositoryClass: AwardRepository::class)]
#[ORM\Table(name: 'daily_checkin_award', options: ['comment' => '签到奖励'])]
class Award implements ApiArrayInterface, \Stringable
{
    use TimestampableAware;
    use SnowflakeKeyAware;
    use BlameableAware;

    #[Ignore]
    #[ORM\ManyToOne(targetEntity: Record::class, inversedBy: 'awards')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Record $record = null;

    #[ORM\ManyToOne(targetEntity: Reward::class, inversedBy: 'awards')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Reward $reward = null;

    #[ORM\ManyToOne(targetEntity: UserInterface::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?UserInterface $user = null;

    public function __toString(): string
    {
        return (string) $this->getId();
    }

    public function getRecord(): ?Record
    {
        return $this->record;
    }

    public function setRecord(?Record $record): void
    {
        $this->record = $record;
    }

    public function getReward(): ?Reward
    {
        return $this->reward;
    }

    public function setReward(?Reward $reward): void
    {
        $this->reward = $reward;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): void
    {
        $this->user = $user;
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'reward' => $this->getReward()?->retrieveApiArray(),
        ];
    }
}
