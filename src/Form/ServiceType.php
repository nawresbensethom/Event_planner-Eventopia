<?php

namespace App\Form;

use App\Entity\Service;
use App\Entity\Utilisateur;
use App\Entity\Categorieservice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du service',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nom du service'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Entrez la description du service'
                ]
            ])
            ->add('tarif', NumberType::class, [
                'label' => 'Tarif (€)',
                'html5' => true,
                'scale' => 2,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 49.99'
                ]
            ])
            ->add('photos', FileType::class, [
                'label' => 'Photo',
                'required' => false,
                'mapped' => false,
                'data_class' => null,
                'attr' => [
                    'class' => 'form-control-file'
                ],
                'help' => 'Choisissez une nouvelle image pour modifier la photo existante'
            ])
            ->add('categorieService', EntityType::class, [
                'class' => Categorieservice::class,
                'choice_label' => 'nom_categorie',
                'label' => 'Catégorie',
                'placeholder' => 'Choisir une catégorie',
                'attr' => ['class' => 'form-select'],
                'required' => true
            ])
            ->add('utilisateur', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'nom',
                'label' => 'Utilisateur associé',
                'attr' => ['class' => 'form-select'],
                'placeholder' => 'Sélectionnez un utilisateur'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'service_item'
        ]);
    }
}