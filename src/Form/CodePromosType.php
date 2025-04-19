<?php

namespace App\Form;

use App\Entity\Code_promos;
use App\Entity\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Doctrine\ORM\EntityManagerInterface;

class CodePromosType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code promotionnel',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: PROMO2025',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Décrivez brièvement loffre promotionnelle...',
                ],
            ])
            ->add('reductionType', ChoiceType::class, [
                'label' => 'Type de réduction',
                'choices' => [
                    'Fixe' => 'fixed',
                    'Pourcentage' => 'percentage',
                ],
                'placeholder' => 'Sélectionnez un type',
                'required' => false,
                'attr' => ['class' => 'form-select'],
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('dateExpiration', DateType::class, [
                'label' => 'Date dexpiration',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('limiteUtilisation', NumberType::class, [
                'label' => 'Limite dutilisation',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 100',
                    'min' => 0,
                ],
            ])
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'nom',
                'label' => 'Service associé',
                'placeholder' => 'Aucun service (optionnel)',
                'required' => false,
                'attr' => ['class' => 'form-select'],
                'choice_value' => 'idService'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Code_promos::class,
            'csrf_protection' => false,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'code_promos_item',
        ]);
    }
}