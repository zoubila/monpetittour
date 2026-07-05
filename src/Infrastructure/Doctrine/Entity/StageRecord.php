<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Entity;

use App\Infrastructure\Doctrine\Repository\StageRecordRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StageRecordRepository::class)]
#[ORM\Table(name: 'stage')]
class StageRecord
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(name: 'stage_number')]
        private int $stageNumber,
        #[ORM\Column(length: 120)]
        private string $startLocation,
        #[ORM\Column(length: 120)]
        private string $finishLocation,
        #[ORM\Column]
        private float $distanceInKilometers,
        #[ORM\Column]
        private int $positiveElevationInMeters,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $mapPath = null,
    ) {
    }

    public function number(): int
    {
        return $this->stageNumber;
    }

    public function stageNumber(): int
    {
        return $this->stageNumber;
    }

    public function startLocation(): string
    {
        return $this->startLocation;
    }

    public function finishLocation(): string
    {
        return $this->finishLocation;
    }

    public function distanceInKilometers(): float
    {
        return $this->distanceInKilometers;
    }

    public function positiveElevationInMeters(): int
    {
        return $this->positiveElevationInMeters;
    }

    public function mapPath(): ?string
    {
        return $this->mapPath;
    }

    public function updateFromImport(
        string $startLocation,
        string $finishLocation,
        float $distanceInKilometers,
        int $positiveElevationInMeters,
        ?string $mapPath,
    ): void {
        $this->startLocation = $startLocation;
        $this->finishLocation = $finishLocation;
        $this->distanceInKilometers = $distanceInKilometers;
        $this->positiveElevationInMeters = $positiveElevationInMeters;
        $this->mapPath = $mapPath;
    }
}
