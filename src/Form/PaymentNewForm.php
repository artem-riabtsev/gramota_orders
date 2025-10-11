<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\Payment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentNewForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('order', EntityType::class, [
                'class' => Order::class,
                'choice_label' => function (Order $order) {
                    return 'Заказ #' . $order->getId() . '— ' . $order->getCustomer()->getName() . '';
                },
                'placeholder' => 'Выберите заказ',
                'attr' => [
                    'class' => 'form-select',
                    'style' => 'display: none;'
                ],
                'label' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
        ]);
    }
}
