<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Application\Handler\GetOfficialRiderGeneralClassificationHandler;
use App\Infrastructure\Doctrine\Entity\ApplicationUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OfficialRiderClassificationController extends AbstractController
{
    #[Route('/classement-general-officiel-coureurs', name: 'official_rider_classification', methods: ['GET'])]
    public function __invoke(GetOfficialRiderGeneralClassificationHandler $classification): Response
    {
        $user = $this->getUser();

        return $this->render('classification/official_riders.html.twig', [
            'standings' => $classification($user instanceof ApplicationUser ? $user : null),
        ]);
    }
}
