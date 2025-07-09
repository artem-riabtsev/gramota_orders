<?php

namespace App\Form;

use App\Entity\Cart;
use App\Entity\Order;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CartForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, ['label' => 'Наименование'])
            ->add('quantity', null, ['label' => 'Количество'])
            ->add('price', null, ['label' => 'Цена'])
            ->add('total_amount', null, ['label' => 'Всего'])
            ->add('order', EntityType::class, [
                'class' => Order::class,
                'choice_label' => 'id',
                'label' => 'Заказ'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cart::class,
        ]);
    }
}
