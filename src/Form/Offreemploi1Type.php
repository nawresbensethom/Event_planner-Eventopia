<?php

namespace App\Form;

use App\Entity\Offreemploi;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class Offreemploi1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre_poste', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(max: 100),
                ],
                'label' => 'Titre du poste'
            ])
            ->add('description', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'label' => 'Description du poste'
            ])
            ->add('type_contrat', ChoiceType::class, [
                'choices' => [
                    'CDI' => 'CDI',
                    'CDD' => 'CDD',
                    'Stage' => 'Stage',
                    'Freelance' => 'Freelance',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'label' => 'Type de contrat'
            ])
            ->add('lieu', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(max: 100),
                ],
                'label' => 'Lieu (gouvernorat)'
            ])
            ->add('salaire', MoneyType::class, [
                'required' => false,
                'currency' => 'TND',
                'label' => 'Salaire',
                'constraints' => [
                    new Assert\PositiveOrZero(),
                ]
            ])
            ->add('date_limite', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date limite',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\GreaterThan('today'),
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Offreemploi::class,
        ]);
    }
}