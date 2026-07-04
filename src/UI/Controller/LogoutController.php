<?php

declare(strict_types=1);

namespace App\UI\Controller;

use LogicException;
use Symfony\Component\Routing\Attribute\Route;

final class LogoutController
{
    #[Route('/deconnexion', name: 'app_logout', methods: ['GET'])]
    public function __invoke(): never
    {
        throw new LogicException('Symfony intercepte cette route via le firewall logout.');
    }
}
