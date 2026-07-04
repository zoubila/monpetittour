<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Entity;

use App\Domain\Enum\RiderSpecialty;
use App\Infrastructure\Doctrine\Repository\RiderRecordRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RiderRecordRepository::class)]
#[ORM\Table(name: 'rider')]
#[ORM\UniqueConstraint(name: 'uniq_rider_slug', columns: ['slug'])]
class RiderRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id; // @phpstan-ignore property.onlyRead

    public function __construct(
        #[ORM\Column(length: 120)]
        private string $slug,
        #[ORM\Column(length: 120)]
        private string $name,
        #[ORM\Column(length: 120)]
        private string $realTeam,
        #[ORM\Column(length: 80)]
        private string $nationality,
        #[ORM\Column]
        private int $marketValueInEuros,
        #[ORM\Column(length: 40, nullable: true, enumType: RiderSpecialty::class)]
        private ?RiderSpecialty $specialty,
        #[ORM\Column(options: ['default' => true])]
        private bool $isStillRacing = true,
    ) {
    }

    public function id(): int
    {
        return $this->id;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function realTeam(): string
    {
        return $this->realTeam;
    }

    public function nationality(): string
    {
        return $this->nationality;
    }

    public function marketValueInEuros(): int
    {
        return $this->marketValueInEuros;
    }

    public function specialty(): ?RiderSpecialty
    {
        return $this->specialty;
    }

    public function isStillRacing(): bool
    {
        return $this->isStillRacing;
    }

    public function markAbandoned(): void
    {
        $this->isStillRacing = false;
    }
}
