<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Application\Handler\GetLeagueDashboardHandler;
use App\Application\Query\GetLeagueDashboardQuery;
use App\Domain\Exception\FantasyLeagueNotFound;
use App\Infrastructure\Doctrine\Entity\ApplicationUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LeagueShowController extends AbstractController
{
    #[Route('/ligues/{id}', name: 'league_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function __invoke(int $id, GetLeagueDashboardHandler $dashboard): Response
    {
        $user = $this->getUser();
        if (!$user instanceof ApplicationUser) {
            throw $this->createAccessDeniedException();
        }

        try {
            return $this->render('league/show.html.twig', [
                'league' => $dashboard(new GetLeagueDashboardQuery($id, $user)),
            ]);
        } catch (FantasyLeagueNotFound) {
            throw $this->createNotFoundException('Ligue introuvable.');
        }
    }
}
