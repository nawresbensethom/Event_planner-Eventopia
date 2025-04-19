<?php

namespace App\Form;

use App\Entity\SignalementPost;
use App\Entity\Post;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class SignalementPostNewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('idPost', EntityType::class, [
                'class' => Post::class,
                'choice_label' => 'titre',
                'label' => 'Publication concernée',
                'required' => true,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('idUtilisateur', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'nom',
                'label' => 'Utilisateur concerné',
                'required' => true,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('raison', TextareaType::class, [
                'label' => 'Motif du signalement',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir le motif du signalement',
                    ]),
                    new Length([
                        'min' => 10,
                        'minMessage' => 'Le motif du signalement doit contenir au moins {{ limit }} caractères',
                        'max' => 1000,
                        'maxMessage' => 'Le motif du signalement ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Décrivez la raison de votre signalement...'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SignalementPost::class,
            'csrf_protection' => true,
        ]);
    }
} 