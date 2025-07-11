<?php

namespace DailyCheckinBundle\Entity;

use AntdCpBundle\Builder\Field\BannerSelector;
use DailyCheckinBundle\Enum\RewardGetType;
use DailyCheckinBundle\Enum\RewardType;
use DailyCheckinBundle\Repository\RewardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Entity(repositoryClass: RewardRepository::class)]
#[ORM\Table(name: 'daily_checkin_reward', options: ['comment' => '签到奖品'])]
class Reward implements \Stringable, ApiArrayInterface, AdminArrayInterface
{
    use TimestampableAware;
    use BlameableAware;
    use SnowflakeKeyAware;

    #[IndexColumn]
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['default' => '0', 'comment' => '次序值'])]
    private ?int $sortNumber = 0;

    public function getSortNumber(): ?int
    {
        return $this->sortNumber;
    }

    public function setSortNumber(?int $sortNumber): self
    {
        $this->sortNumber = $sortNumber;

        return $this;
    }

    public function retrieveSortableArray(): array
    {
        return [
            'sortNumber' => $this->getSortNumber(),
        ];
    }


    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '名称'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING, length: 50, enumType: RewardType::class, options: ['comment' => '类型'])]
    private ?RewardType $type = null;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '奖项'])]
    private ?string $value = null;

    #[IndexColumn]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '签到次数'])]
    private ?int $times = null;

    #[Ignore]
    #[ORM\ManyToOne(targetEntity: Activity::class, inversedBy: 'rewards')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Activity $activity = null;

    /**
     * @var Collection<Award>
     */
    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'reward', targetEntity: Award::class)]
    private Collection $awards;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '总数量'])]
    private ?int $quantity = 0;

    #[ORM\Column(nullable: true, options: ['default' => '0', 'comment' => '每日数量'])]
    private ?int $dayLimit = 0;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '兜底奖项'])]
    private ?bool $isDefault = false;

    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否在奖品列表展示', 'default' => true])]
    private ?bool $canShowPrize = true;

    #[ORM\Column(length: 10, enumType: RewardGetType::class, options: ['comment' => '奖品互斥方式', 'default' => 'and'])]
    private ?RewardGetType $rewardGetType = null;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '签到前图片'])]
    private ?string $beforePicture = null;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '签到后图片'])]
    private array $afterPicture = [];

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '签到前图标'])]
    private ?string $beforeButton = null;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '签到后图标'])]
    private ?string $afterButton = null;

    #[ORM\Column(type: Types::TEXT, length: 255, nullable: true, options: ['comment' => '备注'])]
    private ?string $remark = null;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '其他照片'])]
    private ?array $otherPicture = [];

    public function __construct()
    {
        $this->awards = new ArrayCollection();
    }

    public function __toString()
    {
        if (empty($this->getId())) {
            return '';
        }

        return "{$this->getTimes()}. {$this->getType()->getLabel()} | {$this->getName()} : {$this->getValue()}";
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getType(): ?RewardType
    {
        return $this->type;
    }

    public function setType(RewardType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): self
    {
        $this->remark = $remark;

        return $this;
    }

    public function getActivity(): ?Activity
    {
        return $this->activity;
    }

    public function setActivity(?Activity $activity): self
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * @return Collection<int, Award>
     */
    public function getAwards(): Collection
    {
        return $this->awards;
    }

    public function addAward(Award $award): self
    {
        if (!$this->awards->contains($award)) {
            $this->awards[] = $award;
            $award->setReward($this);
        }

        return $this;
    }

    public function removeAward(Award $award): self
    {
        if ($this->awards->removeElement($award)) {
            // set the owning side to null (unless already changed)
            if ($award->getReward() === $this) {
                $award->setReward(null);
            }
        }

        return $this;
    }

    public function getTimes(): ?int
    {
        return $this->times;
    }

    public function setTimes(?int $times): self
    {
        $this->times = $times;

        return $this;
    }

    public function getBeforePicture(): ?string
    {
        return $this->beforePicture;
    }

    public function setBeforePicture(?string $beforePicture): static
    {
        $this->beforePicture = $beforePicture;

        return $this;
    }

    public function getAfterPicture(): array
    {
        return $this->afterPicture;
    }

    public function setAfterPicture(array $afterPicture): static
    {
        $this->afterPicture = $afterPicture;

        return $this;
    }

    public function getBeforeButton(): ?string
    {
        return $this->beforeButton;
    }

    public function setBeforeButton(string $beforeButton): static
    {
        $this->beforeButton = $beforeButton;

        return $this;
    }

    public function getAfterButton(): ?string
    {
        return $this->afterButton;
    }

    public function setAfterButton(?string $afterButton): static
    {
        $this->afterButton = $afterButton;

        return $this;
    }

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

    public function setRewardGetType(RewardGetType $rewardGetType): static
    {
        $this->rewardGetType = $rewardGetType;

        return $this;
    }

    public function getOtherPicture(): ?array
    {
        return $this->otherPicture;
    }

    public function setOtherPicture(?array $otherPicture): static
    {
        $this->otherPicture = $otherPicture;

        return $this;
    }
}
