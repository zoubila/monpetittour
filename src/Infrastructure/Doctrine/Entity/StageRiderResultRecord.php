<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Entity;

use App\Infrastructure\Doctrine\Repository\StageRiderResultRecordRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StageRiderResultRecordRepository::class)]
#[ORM\Table(name: 'stage_rider_result')]
#[ORM\UniqueConstraint(name: 'uniq_stage_rider_result', columns: ['stage_id', 'rider_id'])]
class StageRiderResultRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id; // @phpstan-ignore property.unused

    public function __construct(
        #[ORM\ManyToOne(targetEntity: StageRecord::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private StageRecord $stage,
        #[ORM\ManyToOne(targetEntity: RiderRecord::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private RiderRecord $rider,
        #[ORM\Column]
        private int $timeInSeconds,
    ) {
    }

    public function stage(): StageRecord
    {
        return $this->stage;
    }

    public function rider(): RiderRecord
    {
        return $this->rider;
    }

    public function timeInSeconds(): int
    {
        return $this->timeInSeconds;
    }
}
