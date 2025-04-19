<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\SignalementPost;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class SignalementPostAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('idUtilisateur', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'nom',
                'label' => 'Utilisateur',
                'required' => true,
                'disabled' => true,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('id_post', EntityType::class, [
                'class' => Post::class,
                'choice_label' => 'titre',
                'label' => 'Publication concernée',
                'required' => true,
                'disabled' => true,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('raison', TextareaType::class, [
                'label' => 'Motif du signalement',
                'required' => true,
                'disabled' => true,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                ]
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'En attente' => SignalementPost::STATUS_EN_ATTENTE,
                    'Traité' => SignalementPost::STATUS_TRAITE,
                ],
                'label' => 'Statut',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner un statut',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SignalementPost::class,
            'csrf_protection' => false,
        ]);
    }
} 