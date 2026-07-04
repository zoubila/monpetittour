<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\UI\Form\LoginFormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class LoginController extends AbstractController
{
    #[Route('/connexion', name: 'app_login', methods: ['GET', 'POST'])]
    public function __invoke(AuthenticationUtils $authenticationUtils, FormFactoryInterface $forms): Response
    {
        $form = $forms->createNamed('', LoginFormType::class, [
            'username' => $authenticationUtils->getLastUsername(),
        ]);

        return $this->render('auth/login.html.twig', [
            'form' => $form,
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }
}
