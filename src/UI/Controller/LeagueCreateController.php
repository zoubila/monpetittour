<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Application\Command\CreateFantasyLeagueCommand;
use App\Application\Handler\CreateFantasyLeagueHandler;
use App\Infrastructure\Doctrine\Entity\ApplicationUser;
use App\UI\Form\LeagueCreateFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LeagueCreateController extends AbstractController
{
    #[Route('/ligues/creation', name: 'league_create', methods: ['GET', 'POST'])]
    public function __invoke(
        Request $request,
        CreateFantasyLeagueHandler $createLeague,
        FormFactoryInterface $forms,
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof ApplicationUser) {
            throw $this->createAccessDeniedException();
        }

        $error = null;
        $form = $forms->createNamed('', LeagueCreateFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{name: string} $data */
            $data = $form->getData();
            $createLeague(new CreateFantasyLeagueCommand($user, trim($data['name'])));

            return $this->redirectToRoute('home');
        }

        return $this->render('league/create.html.twig', [
            'form' => $form,
            'error' => $error,
        ]);
    }
}
