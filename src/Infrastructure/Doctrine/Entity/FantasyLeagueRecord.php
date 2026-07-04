<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Entity;

use App\Infrastructure\Doctrine\Repository\FantasyLeagueRecordRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FantasyLeagueRecordRepository::class)]
#[ORM\Table(name: 'fantasy_league')]
#[ORM\UniqueConstraint(name: 'uniq_fantasy_league_code', columns: ['code'])]
class FantasyLeagueRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // Doctrine owns this generated identifier through reflection.
    private int $id; // @phpstan-ignore property.onlyRead

    /**
     * @var Collection<int, ApplicationUser>
     */
    #[ORM\ManyToMany(targetEntity: ApplicationUser::class, inversedBy: 'leagues')]
    #[ORM\JoinTable(name: 'fantasy_league_participant')]
    private Collection $participants;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: ApplicationUser::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private ApplicationUser $creator,
        #[ORM\Column(length: 120)]
        private string $name,
        #[ORM\Column(length: 12)]
        private string $code,
    ) {
        $this->participants = new ArrayCollection();
        $this->addParticipant($creator);
    }

    public function creator(): ApplicationUser
    {
        return $this->creator;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function addParticipant(ApplicationUser $participant): void
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }
    }

    public function hasParticipant(ApplicationUser $participant): bool
    {
        return $this->participants->contains($participant);
    }

    /**
     * @return list<ApplicationUser>
     */
    public function participants(): array
    {
        return array_values($this->participants->toArray());
    }
}
