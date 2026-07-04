<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Entity;

use App\Infrastructure\Doctrine\Repository\ApplicationUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: ApplicationUserRepository::class)]
#[ORM\Table(name: 'application_user')]
#[ORM\UniqueConstraint(name: 'uniq_application_user_username', columns: ['username'])]
class ApplicationUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // Doctrine owns this generated identifier through reflection.
    private int $id; // @phpstan-ignore property.unused

    #[ORM\Column(length: 80)]
    private string $username;

    #[ORM\Column]
    private string $password;

    /**
     * @var Collection<int, FantasyLeagueRecord>
     */
    #[ORM\ManyToMany(targetEntity: FantasyLeagueRecord::class, mappedBy: 'participants')]
    private Collection $leagues; // @phpstan-ignore property.onlyWritten

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
        $this->leagues = new ArrayCollection();
    }

    public function username(): string
    {
        return $this->username;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function eraseCredentials(): void
    {
    }
}
