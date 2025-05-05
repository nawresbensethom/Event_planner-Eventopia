<?php

namespace App\Form;

use App\Entity\Evenement;
use App\Entity\Reservation;
use App\Entity\Service;
use App\Entity\Code_promos;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('evenement', EntityType::class, [
                'class' => Evenement::class,
                'choice_label' => 'description',
                'label' => 'Événement',
                'required' => true,
                'disabled' => true, // Désactivé car pré-rempli via l'URL
            ])
            ->add('services', EntityType::class, [
                'class' => Service::class,
                'choice_label' => function (Service $service) {
                    return sprintf('%s (€%s)', $service->getNom(), $service->getTarif());
                },
                'multiple' => true,
                'expanded' => true,
                'label' => 'Services',
                'required' => true,
                'choice_attr' => function (Service $service) {
                    return ['data-tarif' => $service->getTarif()];
                },
            ])
            ->add('codePromos', EntityType::class, [
                'class' => Code_promos::class,
                'choice_label' => 'code',
                'label' => 'Code promotionnel',
                'required' => false,
                'placeholder' => 'Aucun code promotionnel',
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Payé' => 'paye',
                    'Non payé' => 'non paye',
                ],
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}