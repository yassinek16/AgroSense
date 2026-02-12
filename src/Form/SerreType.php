<?php

namespace App\Form;

use App\Entity\Serre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints as Assert;



class SerreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Nom de la serre
            ->add('nomSerre', TextType::class, [
                'label' => 'Nom de la serre',
                'required' => false,
                'empty_data' => '',
            ])

            // Localisation
            ->add('localisation', TextType::class, [
                'label' => 'Localisation',
                'required' => false,
                'empty_data' => '',
            ])

            // Surface
            ->add('surface', NumberType::class, [
                'label' => 'Surface (m²)',
                'required' => false,
                'empty_data' => '',
            ])

            // État de la serre
            ->add('etatSerre', ChoiceType::class, [
                'label' => 'État de la serre',
                'choices'  => [
                    'En Culture' => 'culturelle',
                    'En Maintenance' => 'maintenance',
                    'Inactif' => 'inactif',
                ],
                'attr' => [
                    'class' => 'form-select'
                ],
                'required' => false,
                'empty_data' => 'inactif',  // Valeur par défaut si rien n'est sélectionné
                'placeholder' => 'Choisir un état...',  // Optionnel : ajoute une ligne vide par défaut
            ])

            // Date de mise en service
            ->add('dateMiseEnService', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control form-control-lg', 'placeholder' => 'Sélectionnez la date'],
                'html5' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de mise en service ne peut pas être vide.']),
                    new Assert\Type(['type' => 'datetime', 'message' => 'La valeur doit être une date valide.']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Serre::class,
        ]);
    }
}