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
                ],
                'disabled' => $options['post_prefilled'] ?? false
            ])
            ->add('raison', TextareaType::class, [
                'label' => 'Motif du signalement',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 10, 'max' => 500])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Décrivez la raison de votre signalement (10-500 caractères)'
                ]
            ]);

        // On n'ajoute le champ utilisateur que si on n'est pas connecté
        if (!$options['user_prefilled'] ?? false) {
            $builder->add('idUtilisateur', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'nom',
                'label' => 'Utilisateur concerné',
                'required' => true,
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SignalementPost::class,
            'post_prefilled' => false,
            'user_prefilled' => false
        ]);
    }
} 