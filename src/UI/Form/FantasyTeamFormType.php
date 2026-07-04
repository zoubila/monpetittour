<?php

declare(strict_types=1);

namespace App\UI\Form;

use App\Infrastructure\Doctrine\Entity\RiderRecord;
use App\Infrastructure\Doctrine\Repository\RiderRecordRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;

final class FantasyTeamFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [new NotBlank(message: 'Le nom de l’équipe est obligatoire.')],
            ])
            ->add('riders', EntityType::class, [
                'class' => RiderRecord::class,
                'choice_label' => 'name',
                'query_builder' => static fn (RiderRecordRepository $repository) => $repository
                    ->createQueryBuilder('rider')
                    ->orderBy('rider.name', 'ASC'),
                'multiple' => true,
                'expanded' => true,
                'constraints' => [
                    new Count(
                        min: 8,
                        max: 8,
                        exactMessage: 'Une équipe fantasy doit contenir exactement 8 coureurs.',
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
