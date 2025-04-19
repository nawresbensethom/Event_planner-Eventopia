<?php

namespace App\Form;

use App\Entity\Offreemploi;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints as Assert;

class OffreemploiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $governorates = [
            'Tunis', 'Ariana', 'Ben Arous', 'Manouba', 'Nabeul', 'Zaghouan',
            'Béja', 'Bizerte', 'Jendouba', 'Kairouan', 'Kasserine', 'Kebili',
            'Gafsa', 'Gabès', 'Medenine', 'Sfax', 'Sidi Bouzid', 'Siliana',
            'Tataouine', 'Tozeur', 'Mahdia', 'Monastir', 'Sousse', 'Medenine'
        ];

        $builder
            ->add('titre_poste', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le titre du poste ne peut pas être vide.']),
                    new Assert\Length(['max' => 100, 'maxMessage' => 'Le titre du poste ne peut pas dépasser {{ limit }} caractères.']),
                ],
            ])
            ->add('description', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La description ne peut pas être vide.']),
                    new Assert\Length(['min' => 20, 'minMessage' => 'La description doit contenir au moins {{ limit }} caractères.']),
                ],
            ])
            ->add('type_contrat', ChoiceType::class, [
                'choices' => [
                    'CDI' => 'CDI',
                    'CDD' => 'CDD',
                    'Stage' => 'Stage',
                    'Freelance' => 'Freelance',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le type de contrat ne peut pas être vide.']),
                    new Assert\Choice([
                        'choices' => ['CDI', 'CDD', 'Stage', 'Freelance'],
                        'message' => 'Le type de contrat doit être l\'un des choix suivants : {{ choices }}.'
                    ]),
                ],
                'placeholder' => 'Choisir un type de contrat', // Optionnel : ajoute une option de placeholder
            ])        
            ->add('lieu', ChoiceType::class, [
                'choices' => array_combine($governorates, $governorates),
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le lieu ne peut pas être vide.']),
                ],
            ])
            ->add('salaire', NumberType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le salaire ne peut pas être vide.']),
                    new Assert\Positive(['message' => 'Le salaire doit être un nombre positif.']),
                ],
            ])
            ->add('date_limite', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date limite ne peut pas être vide.']),
                    new Assert\GreaterThanOrEqual(['value' => 'today', 'message' => 'La date limite ne peut pas être antérieure à la date actuelle.']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Offreemploi::class,
        ]);
    }
}