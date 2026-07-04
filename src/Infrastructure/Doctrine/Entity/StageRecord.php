<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Entity;

use App\Infrastructure\Doctrine\Repository\StageRecordRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StageRecordRepository::class)]
#[ORM\Table(name: 'stage')]
#[ORM\UniqueConstraint(name: 'uniq_stage_number', columns: ['number'])]
class StageRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id; // @phpstan-ignore property.onlyRead

    public function __construct(
        #[ORM\Column]
        private int $number,
        #[ORM\Column(length: 120)]
        private string $startLocation,
        #[ORM\Column(length: 120)]
        private string $finishLocation,
        #[ORM\Column]
        private int $distanceInKilometers,
        #[ORM\Column]
        private int $positiveElevationInMeters,
    ) {
    }

    public function id(): int
    {
        return $this->id;
    }

    public function number(): int
    {
        return $this->number;
    }

    public function startLocation(): string
    {
        return $this->startLocation;
    }

    public function finishLocation(): string
    {
        return $this->finishLocation;
    }

    public function distanceInKilometers(): int
    {
        return $this->distanceInKilometers;
    }

    public function positiveElevationInMeters(): int
    {
        return $this->positiveElevationInMeters;
    }
}
