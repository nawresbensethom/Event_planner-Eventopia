<?php

namespace App\Form;

use App\Entity\Utilisateur;
use App\Entity\Profil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // User Information
            ->add('nom', TextType::class, [
                'label' => 'Full Name',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your full name']),
                    new Length([
                        'min' => 2, 
                        'max' => 255,
                        'minMessage' => 'The name must be at least {{ limit }} characters long'
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your email']),
                    new Email(['message' => 'Please enter a valid email address']),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'label' => 'Password',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a password']),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ])
           

            // Profile Information
            ->add('description', TextareaType::class, [
                'label' => 'About You',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3],
                'constraints' => [
                    new Length(['max' => 1000])
                ],
                'mapped' => false // This field will be processed separately
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Address',
                'attr' => ['class' => 'form-control'],
                'mapped' => false
            ])
            ->add('specialite', TextType::class, [
                'label' => 'Specialty',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'mapped' => false,
                'constraints' => [
                    new Length(['max' => 255])
                ],
            ])

            // Role Selection
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Prestataire de services' => 'ROLE_PRESTATAIRE',
                    'Organisateur d\'événements' => 'ROLE_ORGANISATEUR',
                    'Administarateur' => 'ROLE_ADMIN',
                ],
                'label' => 'Type de compte',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner votre type de compte']),
                ],
                'mapped' => false
            ])

            // Privileges field
            ->add('privileges', TextareaType::class, [
                'label' => 'Privileges',
                'required' => false,
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Length(['max' => 1000])
                ],
            ])

            // Preferences field
            ->add('preferences', TextareaType::class, [
                'label' => 'Preferences',
                'required' => false,
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Length(['max' => 1000])
                ],
            ])

            // Terms Agreement
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
                'label' => 'I agree to the terms of service',
                'attr' => ['class' => 'form-check-input'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
            'validation_groups' => ['registration']
        ]);
    }
}