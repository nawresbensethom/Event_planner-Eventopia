<?php
namespace App\Form;

use App\Entity\Candidature;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidatureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
           
            ->add('cv', FileType::class, [
                'label' => 'CV (Image)',
                'mapped' => false, // Cela signifie que ce champ n'est pas lié à l'entité
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '5M', // Taille maximale du fichier
                        'mimeTypes' => ['application/pdf', 'image/jpeg', 'image/png'], // Types de fichiers autorisés
                        'mimeTypesMessage' => 'Please upload a valid PDF or image file.',
                    ])
                ],
            ])
            ->add('lettre_motivation', TextareaType::class, [
                'label' => 'Lettre de motivation',
                'required' => true,
                'attr' => ['rows' => 5], // Taille du champ de texte
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Candidature::class, // Associe le formulaire à l'entité Candidature
        ]);
    }
}