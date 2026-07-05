<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Application\Handler\GetGlobalFantasyTeamClassificationHandler;
use App\Infrastructure\Doctrine\Entity\ApplicationUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GlobalFantasyTeamClassificationController extends AbstractController
{
    #[Route('/classement-petit-toureur', name: 'global_fantasy_team_classification', methods: ['GET'])]
    public function __invoke(GetGlobalFantasyTeamClassificationHandler $classification): Response
    {
        $user = $this->getUser();

        if (!$user instanceof ApplicationUser) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('classification/fantasy_teams.html.twig', [
            'standings' => $classification($user),
        ]);
    }
}
