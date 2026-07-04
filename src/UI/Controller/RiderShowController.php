<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Application\Handler\GetRiderDetailsHandler;
use App\Application\Query\GetRiderDetailsQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RiderShowController extends AbstractController
{
    #[Route('/coureurs/{slug}', name: 'rider_show', methods: ['GET'])]
    public function __invoke(string $slug, GetRiderDetailsHandler $getRiderDetails): Response
    {
        $rider = $getRiderDetails(new GetRiderDetailsQuery($slug));

        if ($rider === null) {
            throw $this->createNotFoundException('Coureur introuvable.');
        }

        return $this->render('rider/show.html.twig', [
            'rider' => $rider,
        ]);
    }
}
