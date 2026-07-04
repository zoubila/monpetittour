<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;

final class UsernameAlreadyExists extends DomainException
{
    public function __construct(string $username)
    {
        parent::__construct(sprintf('Le nom d’utilisateur "%s" existe déjà.', $username));
    }
}
