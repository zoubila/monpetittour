<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Application\Handler\GetLeagueTeamCompositionsHandler;
use App\Application\Query\GetLeagueTeamCompositionsQuery;
use App\Domain\Exception\FantasyLeagueNotFound;
use App\Infrastructure\Doctrine\Entity\ApplicationUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LeagueTeamCompositionsController extends AbstractController
{
    #[Route('/ligues/{id}/equipes', name: 'league_team_compositions', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function __invoke(int $id, GetLeagueTeamCompositionsHandler $compositions): Response
    {
        $user = $this->getUser();
        if (!$user instanceof ApplicationUser) {
            throw $this->createAccessDeniedException();
        }

        try {
            return $this->render('league/team_compositions.html.twig', [
                'league' => $compositions(new GetLeagueTeamCompositionsQuery($id, $user)),
                'leagueId' => $id,
            ]);
        } catch (FantasyLeagueNotFound) {
            throw $this->createNotFoundException('error.league_not_found');
        }
    }
}
