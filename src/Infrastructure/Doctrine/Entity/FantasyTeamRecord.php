<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Entity;

use App\Infrastructure\Doctrine\Repository\FantasyTeamRecordRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FantasyTeamRecordRepository::class)]
#[ORM\Table(name: 'fantasy_team')]
#[ORM\UniqueConstraint(name: 'uniq_fantasy_team_owner', columns: ['owner_id'])]
class FantasyTeamRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // Doctrine owns this generated identifier through reflection.
    private int $id; // @phpstan-ignore property.unused

    /**
     * @var Collection<int, RiderRecord>
     */
    #[ORM\ManyToMany(targetEntity: RiderRecord::class)]
    #[ORM\JoinTable(name: 'fantasy_team_rider')]
    private Collection $riders;

    /**
     * @param list<RiderRecord> $riders
     */
    public function __construct(
        #[ORM\ManyToOne(targetEntity: ApplicationUser::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private ApplicationUser $owner,
        #[ORM\Column(length: 120)]
        private string $name,
        array $riders,
    ) {
        $this->riders = new ArrayCollection($riders);
    }

    public function owner(): ApplicationUser
    {
        return $this->owner;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return list<RiderRecord>
     */
    public function riders(): array
    {
        return array_values($this->riders->toArray());
    }
}
