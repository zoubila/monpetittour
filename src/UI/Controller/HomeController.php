<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Application\Handler\GetHomeDashboardHandler;
use App\Infrastructure\Doctrine\Entity\ApplicationUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function __invoke(GetHomeDashboardHandler $dashboard): Response
    {
        $user = $this->getUser();
        if (!$user instanceof ApplicationUser) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('home/index.html.twig', [
            'dashboard' => $dashboard($user),
        ]);
    }
}
