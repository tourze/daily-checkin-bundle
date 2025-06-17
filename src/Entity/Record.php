<?php

namespace DailyCheckinBundle\Entity;

use DailyCheckinBundle\Repository\RecordRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\EasyAdmin\Attribute\Action\Creatable;
use Tourze\EasyAdmin\Attribute\Action\Deletable;
use Tourze\EasyAdmin\Attribute\Action\Editable;
use Tourze\EasyAdmin\Attribute\Action\Exportable;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;

#[AsPermission(title: '打卡记录')]
#[Deletable]
#[Editable]
#[Creatable]
#[Exportable]
#[ORM\Entity(repositoryClass: RecordRepository::class)]
#[ORM\Table(name: 'daily_checkin_record', options: ['comment' => '打卡活动记录'])]
#[ORM\UniqueConstraint(name: 'daily_checkin_record_idx_uniq', columns: ['user_id', 'activity_id', 'checkin_date'])]
class Record implements \Stringable, AdminArrayInterface, ApiArrayInterface
{
    use TimestampableAware;

    #[ExportColumn]
    #[ListColumn(order: -1, sorter: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[CreatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '创建人'])]
    private ?string $createdBy = null;

    #[UpdatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '更新人'])]
    private ?string $updatedBy = null;

    #[CreateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '创建时IP'])]
    private ?string $createdFromIp = null;

    #[UpdateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '更新时IP'])]
    private ?string $updatedFromIp = null;

    #[ExportColumn(title: '活动')]
    #[FormField(title: '活动')]
    #[Filterable(label: '活动')]
    #[ListColumn(title: '活动')]
    #[ORM\ManyToOne(targetEntity: Activity::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Activity $activity = null;

    #[ExportColumn(title: '用户')]
    #[FormField(title: '用户')]
    #[Filterable(label: '用户')]
    #[ListColumn(title: '用户')]
    #[ORM\ManyToOne(targetEntity: UserInterface::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?UserInterface $user = null;

    #[ExportColumn(title: '签到日期')]
    #[FormField]
    #[Filterable]
    #[ListColumn]
    #[IndexColumn]
    #[ORM\Column(type: Types::DATE_MUTABLE, options: ['comment' => '签到日期'])]
    private ?\DateTimeInterface $checkinDate = null;

    #[ExportColumn(title: '用户')]
    #[ListColumn]
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '连续签到', 'dufault' => 0])]
    private ?int $checkinTimes = null;

    /**
     * @var Collection<Award>
     */
    #[ORM\OneToMany(mappedBy: 'record', targetEntity: Award::class)]
    private Collection $awards;

    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '备注'])]
    private ?string $remark = null;

    #[ListColumn]
    #[ORM\Column(options: ['comment' => '是否有奖', 'default' => false])]
    private ?bool $hasAward = null;

    public function __construct()
    {
        $this->awards = new ArrayCollection();
    }

    public function __toString()
    {
        if (empty($this->getId())) {
            return '';
        }

        return "{$this->getActivity()->getTitle()}：{$this->getActivity()->getCheckinType()->getLabel()} {$this->getCheckinTimes()}次";
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function setCreatedFromIp(?string $createdFromIp): self
    {
        $this->createdFromIp = $createdFromIp;

        return $this;
    }

    public function getCreatedFromIp(): ?string
    {
        return $this->createdFromIp;
    }

    public function setUpdatedFromIp(?string $updatedFromIp): self
    {
        $this->updatedFromIp = $updatedFromIp;

        return $this;
    }

    public function getUpdatedFromIp(): ?string
    {
        return $this->updatedFromIp;
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

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCheckinDate(): ?\DateTimeInterface
    {
        return $this->checkinDate;
    }

    public function setCheckinDate(\DateTimeInterface $checkinDate): self
    {
        $this->checkinDate = $checkinDate;

        return $this;
    }

    public function getCheckinTimes(): ?int
    {
        return $this->checkinTimes;
    }

    public function setCheckinTimes(?int $checkinTimes): self
    {
        $this->checkinTimes = $checkinTimes;

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
            $award->setRecord($this);
        }

        return $this;
    }

    public function removeAward(Award $award): self
    {
        if ($this->awards->removeElement($award)) {
            // set the owning side to null (unless already changed)
            if ($award->getRecord() === $this) {
                $award->setRecord(null);
            }
        }

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

    public function setHasAward(bool $hasAward): static
    {
        $this->hasAward = $hasAward;

        return $this;
    }
}
