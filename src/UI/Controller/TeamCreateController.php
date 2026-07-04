<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Application\Command\CreateFantasyTeamCommand;
use App\Application\Handler\CreateFantasyTeamHandler;
use App\Domain\Exception\FantasyTeamAlreadyExists;
use App\Domain\Exception\InvalidFantasyTeam;
use App\Infrastructure\Doctrine\Entity\ApplicationUser;
use App\Infrastructure\Doctrine\Entity\RiderRecord;
use App\UI\Form\FantasyTeamFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TeamCreateController extends AbstractController
{
    #[Route('/mon-equipe/creation', name: 'team_create', methods: ['GET', 'POST'])]
    public function __invoke(
        Request $request,
        CreateFantasyTeamHandler $createTeam,
        FormFactoryInterface $forms,
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof ApplicationUser) {
            throw $this->createAccessDeniedException();
        }

        $error = null;
        $form = $forms->createNamed('', FantasyTeamFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{name: string, riders: iterable<RiderRecord>} $data */
            $data = $form->getData();
            $selectedRiders = [];

            foreach ($data['riders'] as $rider) {
                $selectedRiders[] = $rider->id();
            }

            try {
                $createTeam(new CreateFantasyTeamCommand($user, trim($data['name']), $selectedRiders));

                return $this->redirectToRoute('home');
            } catch (FantasyTeamAlreadyExists|InvalidFantasyTeam $exception) {
                $error = $exception->getMessage();
            }
        }

        return $this->render('team/create.html.twig', [
            'form' => $form,
            'error' => $error,
        ]);
    }
}
