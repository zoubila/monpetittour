<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Application\Handler\ListRidersHandler;
use App\Infrastructure\Doctrine\Entity\ApplicationUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RiderIndexController extends AbstractController
{
    #[Route('/coureurs', name: 'rider_index', methods: ['GET'])]
    public function __invoke(ListRidersHandler $listRiders): Response
    {
        $user = $this->getUser();

        return $this->render('rider/index.html.twig', [
            'riders' => $listRiders($user instanceof ApplicationUser ? $user : null),
        ]);
    }
}
