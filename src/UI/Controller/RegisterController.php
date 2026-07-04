<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Application\Command\RegisterUserCommand;
use App\Application\Handler\RegisterUserHandler;
use App\Domain\Exception\UsernameAlreadyExists;
use App\UI\Form\RegisterFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RegisterController extends AbstractController
{
    #[Route('/inscription', name: 'app_register', methods: ['GET', 'POST'])]
    public function __invoke(
        Request $request,
        RegisterUserHandler $registerUser,
        Security $security,
        FormFactoryInterface $forms,
    ): Response {
        $error = null;
        $form = $forms->createNamed('', RegisterFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{username: string, password: string} $data */
            $data = $form->getData();
            $username = trim($data['username']);
            $password = $data['password'];

            try {
                $user = $registerUser(new RegisterUserCommand($username, $password));

                return $security->login($user, 'form_login', 'main');
            } catch (UsernameAlreadyExists $exception) {
                $error = $exception->getMessage();
            }
        }

        return $this->render('auth/register.html.twig', [
            'form' => $form,
            'error' => $error,
        ]);
    }
}
