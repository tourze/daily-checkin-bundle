<?php

namespace DailyCheckinBundle\Entity;

use DailyCheckinBundle\Enum\RewardGetType;
use DailyCheckinBundle\Enum\RewardType;
use DailyCheckinBundle\Repository\RewardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

/**
 * @implements ApiArrayInterface<string, mixed>
 * @implements AdminArrayInterface<string, mixed>
 */
#[ORM\Entity(repositoryClass: RewardRepository::class)]
#[ORM\Table(name: 'daily_checkin_reward', options: ['comment' => '签到奖品'])]
class Reward implements \Stringable, ApiArrayInterface, AdminArrayInterface
{
    use TimestampableAware;
    use BlameableAware;
    use SnowflakeKeyAware;

    #[Assert\PositiveOrZero]
    #[IndexColumn]
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['default' => '0', 'comment' => '次序值'])]
    private ?int $sortNumber = 0;

    public function getSortNumber(): ?int
    {
        return $this->sortNumber;
    }

    public function setSortNumber(?int $sortNumber): void
    {
        $this->sortNumber = $sortNumber;
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveSortableArray(): array
    {
        return [
            'sortNumber' => $this->getSortNumber(),
        ];
    }

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '名称'])]
    private ?string $name = null;

    #[Assert\NotNull]
    #[Assert\Choice(callback: [RewardType::class, 'cases'])]
    #[ORM\Column(type: Types::STRING, length: 50, enumType: RewardType::class, options: ['comment' => '类型'])]
    private ?RewardType $type = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '奖项'])]
    private ?string $value = null;

    #[Assert\NotNull]
    #[Assert\Positive]
    #[IndexColumn]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '签到次数'])]
    private ?int $times = null;

    #[Ignore]
    #[ORM\ManyToOne(targetEntity: Activity::class, inversedBy: 'rewards')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Activity $activity = null;

    /**
     * @var Collection<int, Award>
     */
    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'reward', targetEntity: Award::class)]
    private Collection $awards;

    #[Assert\PositiveOrZero]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '总数量'])]
    private ?int $quantity = 0;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['default' => '0', 'comment' => '每日数量'])]
    private ?int $dayLimit = 0;

    #[Assert\Type(type: 'bool')]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '兜底奖项'])]
    private ?bool $isDefault = false;

    #[Assert\NotNull]
    #[Assert\Type(type: 'bool')]
    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否在奖品列表展示', 'default' => true])]
    private ?bool $canShowPrize = true;

    #[Assert\Choice(callback: [RewardGetType::class, 'cases'])]
    #[ORM\Column(length: 10, enumType: RewardGetType::class, options: ['comment' => '奖品互斥方式', 'default' => 'and'])]
    private ?RewardGetType $rewardGetType = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '签到前图片'])]
    private ?string $beforePicture = null;

    /**
     * @var array<mixed>|null
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '签到后图片'])]
    private ?array $afterPicture = [];

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '签到前图标'])]
    private ?string $beforeButton = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '签到后图标'])]
    private ?string $afterButton = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(type: Types::TEXT, length: 255, nullable: true, options: ['comment' => '备注'])]
    private ?string $remark = null;

    /**
     * @var array<mixed>|null
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '其他照片'])]
    private ?array $otherPicture = [];

    public function __construct()
    {
        $this->awards = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (null === $this->getId()) {
            return '';
        }

        $type = $this->getType();
        if (null === $type) {
            return "{$this->getTimes()}. {$this->getName()} : {$this->getValue()}";
        }

        return "{$this->getTimes()}. {$type->getLabel()} | {$this->getName()} : {$this->getValue()}";
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): void
    {
        $this->value = $value;
    }

    public function getType(): ?RewardType
    {
        return $this->type;
    }

    public function setType(?RewardType $type): void
    {
        $this->type = $type;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    public function getActivity(): ?Activity
    {
        return $this->activity;
    }

    public function setActivity(?Activity $activity): void
    {
        $this->activity = $activity;
    }

    /**
     * @return Collection<int, Award>
     */
    public function getAwards(): Collection
    {
        return $this->awards;
    }

    public function addAward(Award $award): void
    {
        if (!$this->awards->contains($award)) {
            $this->awards->add($award);
            $award->setReward($this);
        }
    }

    public function removeAward(Award $award): void
    {
        if ($this->awards->removeElement($award)) {
            // set the owning side to null (unless already changed)
            if ($award->getReward() === $this) {
                $award->setReward(null);
            }
        }
    }

    public function getTimes(): ?int
    {
        return $this->times;
    }

    public function setTimes(?int $times): void
    {
        $this->times = $times;
    }

    public function getBeforePicture(): ?string
    {
        return $this->beforePicture;
    }

    public function setBeforePicture(?string $beforePicture): void
    {
        $this->beforePicture = $beforePicture;
    }

    /**
     * @return array<mixed>|null
     */
    public function getAfterPicture(): ?array
    {
        return $this->afterPicture;
    }

    /**
     * @param array<mixed> $afterPicture
     */
    public function setAfterPicture(?array $afterPicture): void
    {
        $this->afterPicture = $afterPicture;
    }

    public function getBeforeButton(): ?string
    {
        return $this->beforeButton;
    }

    public function setBeforeButton(?string $beforeButton): void
    {
        $this->beforeButton = $beforeButton;
    }

    public function getAfterButton(): ?string
    {
        return $this->afterButton;
    }

    public function setAfterButton(?string $afterButton): void
    {
        $this->afterButton = $afterButton;
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
            'name' => $this->getName(),
            'times' => $this->getTimes(),
            'type' => $this->getType()?->value,
            'remark' => $this->getRemark(),
            'value' => $this->getValue(),
            'beforePicture' => $this->getBeforePicture(),
            'afterPicture' => $this->getAfterPicture(),
            'beforeButton' => $this->getBeforeButton(),
            'afterButton' => $this->getAfterButton(),
            'dayLimit' => $this->getDayLimit(),
            'canShowPrize' => $this->getCanShowPrize(),
            'rewardGetType' => $this->getRewardGetType()?->value,
            'otherPicture' => $this->getOtherPicture(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveAdminArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'name' => $this->getName(),
            'times' => $this->getTimes(),
            'type' => $this->getType(),
            'value' => $this->getValue(),
            'remark' => $this->getRemark(),
            'beforePicture' => $this->getBeforePicture(),
            'afterPicture' => $this->getAfterPicture(),
            'beforeButton' => $this->getBeforeButton(),
            'afterButton' => $this->getAfterButton(),
            'quantity' => $this->getQuantity(),
            'dayLimit' => $this->getDayLimit(),
            'isDefault' => $this->getIsDefault(),
            'canShowPrize' => $this->getCanShowPrize(),
            'rewardGetType' => $this->getRewardGetType(),
            'otherPicture' => $this->getOtherPicture(),
            ...$this->retrieveSortableArray(),
        ];
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getDayLimit(): ?int
    {
        return $this->dayLimit;
    }

    public function setDayLimit(?int $dayLimit): void
    {
        $this->dayLimit = $dayLimit;
    }

    public function getIsDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(?bool $isDefault): void
    {
        $this->isDefault = $isDefault;
    }

    public function getCanShowPrize(): ?bool
    {
        return $this->canShowPrize;
    }

    public function setCanShowPrize(?bool $canShowPrize): void
    {
        $this->canShowPrize = $canShowPrize;
    }

    public function getRewardGetType(): ?RewardGetType
    {
        return $this->rewardGetType;
    }

    public function setRewardGetType(?RewardGetType $rewardGetType): void
    {
        $this->rewardGetType = $rewardGetType;
    }

    /**
     * @return array<mixed>|null
     */
    public function getOtherPicture(): ?array
    {
        return $this->otherPicture;
    }

    /**
     * @param array<mixed>|null $otherPicture
     */
    public function setOtherPicture(?array $otherPicture): void
    {
        $this->otherPicture = $otherPicture;
    }
}
