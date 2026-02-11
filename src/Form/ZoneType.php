<?php

namespace App\Form;

use App\Entity\Zone;
use App\Entity\Serre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class ZoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomZone', TextType::class, [
                'label' => 'Nom de la zone',
                'required' => false,
                'empty_data' => '',
            ])

            ->add('typeZone', TextType::class, [
                'label' => 'Type de zone',
                'required' => false,
                'empty_data' => '',
            ])

            ->add('superficie', NumberType::class, [
                'label' => 'Superficie (m²)',
                'required' => false,
                'empty_data' => '0',
            ])

            ->add('etatZone', TextType::class, [
                'label' => 'État de la zone',
                'required' => false,
                'empty_data' => '',
            ])

            ->add('cultureAssociee', TextType::class, [
                'label' => 'Culture associée',
                'required' => false,
                'empty_data' => '',
            ])

            // ⭐ RELATION AVEC SERRE
            ->add('serre', EntityType::class, [
                'class' => Serre::class,
                'choice_label' => 'nomSerre',
                'placeholder' => '--- Choisir une serre ---',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Zone::class,
        ]);
    }
}
