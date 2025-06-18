<?php

namespace DailyCheckinBundle\Entity;

use DailyCheckinBundle\Repository\AwardRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;

/**
 * 这里的award取的是名词的意思，有颁发之类的意思，跟Lottery那边有一点点区别
 */
#[ORM\Entity(repositoryClass: AwardRepository::class)]
#[ORM\Table(name: 'daily_checkin_award', options: ['comment' => '签到奖励'])]
class Award implements ApiArrayInterface, \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    use BlameableAware;

    #[Ignore]
    #[ORM\ManyToOne(targetEntity: Record::class, inversedBy: 'awards')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Record $record = null;

    #[ORM\ManyToOne(targetEntity: Reward::class, inversedBy: 'awards')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Reward $reward = null;

    #[Filterable(label: '用户')]
    #[ORM\ManyToOne(targetEntity: UserInterface::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?UserInterface $user = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->getId() ?? '';
    }

    public function getRecord(): ?Record
    {
        return $this->record;
    }

    public function setRecord(?Record $record): self
    {
        $this->record = $record;

        return $this;
    }

    public function getReward(): ?Reward
    {
        return $this->reward;
    }

    public function setReward(?Reward $reward): self
    {
        $this->reward = $reward;

        return $this;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

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
