<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Application\Handler\GetRiderDetailsHandler;
use App\Application\Query\GetRiderDetailsQuery;
use App\Infrastructure\Doctrine\Entity\ApplicationUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RiderShowController extends AbstractController
{
    #[Route('/coureurs/{slug}', name: 'rider_show', methods: ['GET'])]
    public function __invoke(string $slug, GetRiderDetailsHandler $getRiderDetails): Response
    {
        $user = $this->getUser();
        $rider = $getRiderDetails(
            new GetRiderDetailsQuery($slug),
            $user instanceof ApplicationUser ? $user : null,
        );

        if ($rider === null) {
            throw $this->createNotFoundException('error.rider_not_found');
        }

        return $this->render('rider/show.html.twig', [
            'rider' => $rider,
        ]);
    }
}
