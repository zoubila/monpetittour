<?php

declare(strict_types=1);

namespace App\UI\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class RegisterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'constraints' => [new NotBlank(message: 'Le nom d’utilisateur est obligatoire.')],
            ])
            ->add('password', PasswordType::class, [
                'constraints' => [new NotBlank(message: 'Le mot de passe est obligatoire.')],
            ]);
    }
}
