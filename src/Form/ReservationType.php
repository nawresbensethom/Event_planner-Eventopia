<?php

namespace App\Form;

use App\Entity\Evenement;
use App\Entity\Reservation;
use App\Entity\Service;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Choice;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('montant_total', null, [
                'constraints' => [
                    new Positive(),
                ],
            ])
            ->add('statut', null, [
                'constraints' => [
                    new Choice([
                        'choices' => ['planifie', 'confirme', 'annule', 'termine'],
                        'message' => 'Choisissez un statut valide.',
                    ]),
                ],
            ])
            ->add('evenement', EntityType::class, [
                'class' => Evenement::class,
                'choice_label' => 'nom', // Afficher le nom plutôt que l'ID
            ])
            ->add('client', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'email', // Afficher l'email plutôt que l'ID
            ])
            ->add('serviceIds', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'nom', // Afficher le nom du service
                'multiple' => true,
                'mapped' => true,
                'required' => false,
                'label' => 'Services',
                'attr' => [
                    'class' => 'select2' // Si vous utilisez Select2
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
