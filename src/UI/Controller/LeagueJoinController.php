<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Application\Command\JoinFantasyLeagueCommand;
use App\Application\Handler\JoinFantasyLeagueHandler;
use App\Domain\Exception\FantasyLeagueNotFound;
use App\Infrastructure\Doctrine\Entity\ApplicationUser;
use App\UI\Form\LeagueJoinFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LeagueJoinController extends AbstractController
{
    #[Route('/ligues/rejoindre', name: 'league_join', methods: ['GET', 'POST'])]
    public function __invoke(
        Request $request,
        JoinFantasyLeagueHandler $joinLeague,
        FormFactoryInterface $forms,
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof ApplicationUser) {
            throw $this->createAccessDeniedException();
        }

        $error = null;
        $form = $forms->createNamed('', LeagueJoinFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{code: string} $data */
            $data = $form->getData();
            $code = strtoupper(trim($data['code']));

            try {
                $joinLeague(new JoinFantasyLeagueCommand($user, $code));

                return $this->redirectToRoute('home');
            } catch (FantasyLeagueNotFound $exception) {
                $error = $exception->getMessage();
            }
        }

        return $this->render('league/join.html.twig', [
            'form' => $form,
            'error' => $error,
        ]);
    }
}
