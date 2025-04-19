<?php
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

$builder
    ->add('role', ChoiceType::class, [
        'choices' => [
            'Organisateur' => 'ROLE_ORGANISATEUR',
            'Prestataire' => 'ROLE_PRESTATAIRE',
            'Admin' => 'ROLE_ADMIN',
     ],
        'label' => 'Rôle de l\'utilisateur',
        'attr' => [
            'class' => 'form-select'
        ]
        ]);
        

