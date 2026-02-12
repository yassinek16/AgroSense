<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'First Name',
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 100,
                        'maxMessage' => 'First name must be no more than 100 characters',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'John',
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last Name',
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 100,
                        'maxMessage' => 'Last name must be no more than 100 characters',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Doe',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email Address',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Email is required',
                    ]),
                    new Email([
                        'message' => 'Please provide a valid email address',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'user@example.com',
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Role',
                'choices' => [
                    'Agricultor' => 'ROLE_AGRICULTOR',
                    'Technicien' => 'ROLE_TECHNICIEN',
                    'Administrator' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please assign at least one role',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('statutCompte', ChoiceType::class, [
                'label' => 'Account Status',
                'choices' => [
                    'Active' => 'active',
                    'Away' => 'away',
                    'Offline' => 'offline',
                    'Suspended' => 'suspended',
                ],
                'required' => false,
                'placeholder' => 'Select a status',
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('subscriptionStatus', ChoiceType::class, [
                'label' => 'Subscription Status',
                'choices' => [
                    'Active' => 'active',
                    'Expired' => 'expired',
                ],
                'required' => false,
                'placeholder' => 'Select subscription status',
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('isVerified', CheckboxType::class, [
                'label' => 'Email Verified',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
