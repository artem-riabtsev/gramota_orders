<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\Payment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', null, ['label' => 'Дата'])
            ->add('amount', null, ['label' => 'Сумма платежа'])
            ->add('order', EntityType::class, [
                'class' => Order::class,
                'label' => 'Номер заказа',
                'choice_label' => 'id',
                'disabled' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
        ]);
    }
}
