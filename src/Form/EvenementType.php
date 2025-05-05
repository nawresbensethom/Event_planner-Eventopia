<?php

namespace App\Form;

use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Form\CallbackTransformer;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_evenement', TextType::class, [
                'label' => 'Nom de l\'événement',
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
            ])
            ->add('date_evenement', DateType::class, [
                'label' => 'Date de l\'événement',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('duree', NumberType::class, [
                'label' => 'Durée (minutes)',
                'required' => true,
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Concert' => 'concert',
                    'Conférence' => 'conférence',
                    'Atelier' => 'atelier',
                    'Exposition' => 'exposition',
                    'Autre' => 'autre',
                ],
                'required' => true,
            ])
            ->add('statut3', ChoiceType::class, [
                'label' => 'Visibilité',
                'choices' => [
                    'Public' => 'public',
                    'Privé' => 'privé',
                ],
                'required' => true,
            ])
            ->add('rue', TextType::class, [
                'label' => 'Rue',
                'required' => false,
            ])
            ->add('code_postal', TextType::class, [
                'label' => 'Code postal',
                'required' => false,
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'required' => false,
            ])
            ->add('latitude', HiddenType::class, [
                'required' => false,
            ])
            ->add('longitude', HiddenType::class, [
                'required' => false,
            ])
            ->add('statut', HiddenType::class, [
                'data' => 'non reserve',
                'required' => true,
            ])
            ->add('statut2', HiddenType::class, [
                'data' => 'en preparation',
                'required' => true,
            ])
            ->add('liste_invites', TextType::class, [
                'label' => 'Liste des invités (emails séparés par des virgules)',
                'required' => false,
                'attr' => ['placeholder' => 'email1@example.com,email2@example.com'],
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => 'Image de l\'événement',
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Supprimer l\'image',
                'download_uri' => false,
                'image_uri' => true,
            ]);

        // DataTransformer for liste_invites
        $builder->get('liste_invites')
            ->addModelTransformer(new CallbackTransformer(
                function (?array $listeInvites): string {
                    return $listeInvites ? implode(', ', $listeInvites) : '';
                },
                function (?string $listeInvitesString): ?array {
                    if (empty($listeInvitesString)) {
                        return [];
                    }
                    return array_filter(array_map('trim', explode(',', $listeInvitesString)));
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}