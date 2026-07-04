<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Command\RegisterUserCommand;
use App\Domain\Exception\UsernameAlreadyExists;
use App\Infrastructure\Doctrine\Entity\ApplicationUser;
use App\Infrastructure\Doctrine\Repository\ApplicationUserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class RegisterUserHandler
{
    public function __construct(
        private ApplicationUserRepository $users,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function __invoke(RegisterUserCommand $command): ApplicationUser
    {
        $username = trim($command->username);

        if ($this->users->findOneByUsername($username) instanceof ApplicationUser) {
            throw new UsernameAlreadyExists($username);
        }

        $user = new ApplicationUser($username, '');
        $hashedPassword = $this->passwordHasher->hashPassword($user, $command->plainPassword);
        $user = new ApplicationUser($username, $hashedPassword);

        $this->users->save($user);

        return $user;
    }
}
