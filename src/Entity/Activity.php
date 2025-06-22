<?php

namespace DailyCheckinBundle\Entity;

use DailyCheckinBundle\Enum\CheckinType;
use DailyCheckinBundle\Repository\ActivityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
#[ORM\Table(name: 'daily_checkin_activity', options: ['comment' => '打卡活动'])]
class Activity implements \Stringable, ApiArrayInterface
{
    use TimestampableAware;
    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '分享路径'])]
    private ?string $sharePath = null;

    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '分享标题'])]
    private ?string $shareTitle = null;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '分享图片'])]
    private ?string $sharePicture = null;

    public function getShareTitle(): ?string
    {
        return $this->shareTitle;
    }

    public function setShareTitle(?string $shareTitle): void
    {
        $this->shareTitle = $shareTitle;
    }

    public function getSharePicture(): ?string
    {
        return $this->sharePicture;
    }

    public function setSharePicture(?string $sharePicture): void
    {
        $this->sharePicture = $sharePicture;
    }

    public function getSharePath(): ?string
    {
        return $this->sharePath;
    }

    public function setSharePath(?string $sharePath): void
    {
        $this->sharePath = $sharePath;
    }

    public function retrieveWechatShareFriendConfig(): array
    {
        return [
            'shareTitle' => $this->getShareTitle(),
            'sharePicture' => $this->getSharePicture(),
            'sharePath' => $this->getSharePath(),
        ];
    }

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    use BlameableAware;

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => '标题'])]
    private string $title;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '开始时间'])]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '结束时间'])]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '活动周期(次)', 'default' => 1])]
    private int $times = 1;

    /**
     * @var Collection<Reward>
     */
    #[ORM\OneToMany(targetEntity: Reward::class, mappedBy: 'activity', cascade: ['persist'], orphanRemoval: true)]
    private Collection $rewards;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: CheckinType::class, options: ['comment' => '签到类型'])]
    private ?CheckinType $checkinType = null;

    #[Groups(['admin_curd', 'restful_read'])]
    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '朋友圈分享标题'])]
    private ?string $zoneShareTitle = null;

    #[Groups(['admin_curd', 'restful_read'])]
    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '朋友圈分享图片'])]
    private ?string $zoneSharePicture = null;

    public function getZoneShareTitle(): ?string
    {
        return $this->zoneShareTitle;
    }

    public function setZoneShareTitle(?string $zoneShareTitle): void
    {
        $this->zoneShareTitle = $zoneShareTitle;
    }

    public function getZoneSharePicture(): ?string
    {
        return $this->zoneSharePicture;
    }

    public function setZoneSharePicture(?string $zoneSharePicture): void
    {
        $this->zoneSharePicture = $zoneSharePicture;
    }

    public function retrieveWechatShareZoneConfig(): array
    {
        return [
            'zoneShareTitle' => $this->getZoneShareTitle(),
            'zoneSharePicture' => $this->getZoneSharePicture(),
        ];
    }

    public function __construct()
    {
        $this->rewards = new ArrayCollection();
    }

    public function __toString(): string
    {
        if ($this->getId() === null) {
            return '';
        }

        return $this->getTitle();
    }

    public function getId(): ?string
    {
        return $this->id;
    }


    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getTimes(): ?int
    {
        return $this->times;
    }

    public function setTimes(int $times): self
    {
        $this->times = $times;

        return $this;
    }

    /**
     * @return Collection<int, Reward>
     */
    public function getRewards(): Collection
    {
        return $this->rewards;
    }

    public function addReward(Reward $reward): self
    {
        if (!$this->rewards->contains($reward)) {
            $this->rewards[] = $reward;
            $reward->setActivity($this);
        }

        return $this;
    }

    public function removeReward(Reward $reward): self
    {
        if ($this->rewards->removeElement($reward)) {
            // set the owning side to null (unless already changed)
            if ($reward->getActivity() === $this) {
                $reward->setActivity(null);
            }
        }

        return $this;
    }

    public function getCheckinType(): ?CheckinType
    {
        return $this->checkinType;
    }

    public function setCheckinType(?CheckinType $checkinType): self
    {
        $this->checkinType = $checkinType;

        return $this;
    }

    public function retrieveApiArray(): array
    {
        $rewards = [];
        foreach ($this->getRewards() as $reward) {
            $rewards[] = $reward->retrieveApiArray();
        }

        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            ...$this->retrieveWechatShareFriendConfig(),
            ...$this->retrieveWechatShareZoneConfig(),
            'title' => $this->getTitle(),
            'times' => $this->getTimes(),
            'rewards' => $rewards,
        ];
    }}
