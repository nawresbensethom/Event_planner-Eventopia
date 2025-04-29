<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\All;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a title',
                    ]),
                    new Length([
                        'min' => 5,
                        'minMessage' => 'Your title should be at least {{ limit }} characters',
                        'max' => 100,
                        'maxMessage' => 'Your title cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Enter a captivating title'
                ]
            ])
            ->add('contenu', TextareaType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter some content',
                    ]),
                    new Length([
                        'min' => 20,
                        'minMessage' => 'Your content should be at least {{ limit }} characters',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Share your story...',
                    'rows' => 6
                ]
            ])
            ->add('photos', FileType::class, [
                'label' => 'Photos',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'accept' => 'image/*',
                    'data-max-size' => '5242880', // 5MB in bytes
                ],
                'constraints' => [
                    new All([
                        'constraints' => [
                            new File([
                                'maxSize' => '5M',
                                'mimeTypes' => [
                                    'image/jpeg',
                                    'image/png',
                                    'image/gif'
                                ],
                                'mimeTypesMessage' => 'Please upload a valid image file (JPG, PNG, GIF)',
                                'maxSizeMessage' => 'The file is too large ({{ size }} {{ suffix }}). Maximum allowed size is {{ limit }} {{ suffix }}.',
                            ])
                        ]
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
            'allow_file_upload' => true,
            'csrf_protection' => true,
        ]);
    }
}
