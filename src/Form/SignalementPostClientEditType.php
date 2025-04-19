<?php

namespace App\Form;

use App\Entity\SignalementPost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class SignalementPostClientEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
                    'class' => 'form-control form-textarea',
                    'rows' => 4,
                    'placeholder' => 'Modifiez votre motif de signalement ici...'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SignalementPost::class,
        ]);
    }
} 