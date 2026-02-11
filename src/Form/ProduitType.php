<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints as Assert;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du produit',
                'attr' => [
                    'placeholder' => 'Entrez le nom du produit',
                    'minlength' => 3,
                    'maxlength' => 255,
                    'class' => 'form-control'
                ],
                'help' => 'Le nom doit contenir entre 3 et 255 caractères'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Décrivez votre produit en détail...',
                    'rows' => 4,
                    'minlength' => 10,
                    'class' => 'form-control'
                ],
                'help' => 'La description doit contenir au moins 10 caractères'
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix (DT)',
                'scale' => 2,
                'attr' => [
                    'placeholder' => '0.00',
                    'min' => 0.01,
                    'max' => 9999.99,
                    'step' => '0.01',
                    'class' => 'form-control'
                ],
                'help' => 'Le prix doit être positif et inférieur à 10 000 DT'
            ])
            ->add('quantiteStock', NumberType::class, [
                'label' => 'Quantité en stock',
                'attr' => [
                    'placeholder' => '0',
                    'min' => 0,
                    'max' => 9999,
                    'class' => 'form-control'
                ],
                'help' => 'La quantité doit être un nombre positif inférieur à 10 000'
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image du produit',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'accept' => 'image/*',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, GIF, WebP)',
                        'maxSizeMessage' => 'L\'image ne doit pas dépasser 2 Mo',
                    ])
                ],
                'help' => 'Formats acceptés: JPEG, PNG, GIF, WebP (max 2 Mo)'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
