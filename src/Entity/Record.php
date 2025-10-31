<?php

namespace DailyCheckinBundle\Entity;

use DailyCheckinBundle\Repository\RecordRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

/**
 * @implements AdminArrayInterface<string, mixed>
 * @implements ApiArrayInterface<string, mixed>
 */
#[ORM\Entity(repositoryClass: RecordRepository::class)]
#[ORM\Table(name: 'daily_checkin_record', options: ['comment' => '打卡活动记录'])]
#[ORM\UniqueConstraint(name: 'daily_checkin_record_idx_uniq', columns: ['user_id', 'activity_id', 'checkin_date'])]
class Record implements \Stringable, AdminArrayInterface, ApiArrayInterface
{
    use TimestampableAware;
    use SnowflakeKeyAware;
    use BlameableAware;
    use IpTraceableAware;

    #[ORM\ManyToOne(targetEntity: Activity::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Activity $activity = null;

    #[ORM\ManyToOne(targetEntity: UserInterface::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?UserInterface $user = null;

    #[Assert\NotNull]
    #[IndexColumn]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, options: ['comment' => '签到日期'])]
    private ?\DateTimeInterface $checkinDate = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '连续签到', 'default' => 0])]
    private ?int $checkinTimes = null;

    /**
     * @var Collection<int, Award>
     */
    #[ORM\OneToMany(mappedBy: 'record', targetEntity: Award::class)]
    private Collection $awards;

    #[Assert\Length(max: 100)]
    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '备注'])]
    private ?string $remark = null;

    #[Assert\NotNull]
    #[ORM\Column(options: ['comment' => '是否有奖', 'default' => false])]
    private ?bool $hasAward = null;

    public function __construct()
    {
        $this->awards = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (null === $this->getId()) {
            return '';
        }

        $activity = $this->getActivity();
        if (null === $activity) {
            return '';
        }

        $checkinType = $activity->getCheckinType();
        if (null === $checkinType) {
            return $activity->getTitle();
        }

        return "{$activity->getTitle()}：{$checkinType->getLabel()} {$this->getCheckinTimes()}次";
    }

    public function getActivity(): ?Activity
    {
        return $this->activity;
    }

    public function setActivity(?Activity $activity): void
    {
        $this->activity = $activity;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): void
    {
        $this->user = $user;
    }

    public function getCheckinDate(): ?\DateTimeInterface
    {
        return $this->checkinDate;
    }

    public function setCheckinDate(\DateTimeInterface $checkinDate): void
    {
        $this->checkinDate = $checkinDate;
    }

    public function getCheckinTimes(): ?int
    {
        return $this->checkinTimes;
    }

    public function setCheckinTimes(?int $checkinTimes): void
    {
        $this->checkinTimes = $checkinTimes;
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
            $award->setRecord($this);
        }
    }

    public function removeAward(Award $award): void
    {
        if ($this->awards->removeElement($award)) {
            // set the owning side to null (unless already changed)
            if ($award->getRecord() === $this) {
                $award->setRecord(null);
            }
        }
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveAdminArray(): array
    {
        return [
            'id' => $this->getId(),
            'checkinDate' => $this->getCheckinDate()?->format('Y-m-d'),
            'checkinTimes' => $this->getCheckinTimes(),
            'hasAward' => $this->hasAward(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveApiArray(): array
    {
        $awards = [];
        foreach ($this->getAwards() as $award) {
            $awards[] = $award->retrieveApiArray();
        }

        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'checkinDate' => $this->getCheckinDate()?->format('Y-m-d'),
            'checkinTimes' => $this->getCheckinTimes(),
            'hasAward' => $this->hasAward(),
            'awards' => $awards,
        ];
    }

    public function hasAward(): ?bool
    {
        return $this->hasAward;
    }

    public function setHasAward(bool $hasAward): void
    {
        $this->hasAward = $hasAward;
    }
}
