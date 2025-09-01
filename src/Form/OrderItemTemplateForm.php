<?php

namespace App\Form;

use App\Entity\Price;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderItemTemplateForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('price', EntityType::class, [
                'class' => Price::class,
                'label' => 'Шаблон',
                'choice_label' => 'description',
                'placeholder' => 'Выберите шаблон',
                'choice_value' => 'id'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'price_choices' => [],
        ]);
    }
}
