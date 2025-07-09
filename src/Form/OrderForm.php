<?php

namespace App\Form;

use App\Entity\Customer;
use App\Entity\Order;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', null, ['label' => 'Дата заказа'])
            ->add('amount', null, ['label' => 'Сумма заказа'])
            ->add('payment_date', null, ['label' => 'Дата оплаты'])
            ->add('payment_amount', null, ['label' => 'Сумма оплаты'])
            ->add('customer', EntityType::class, [
                'class' => Customer::class,
                'choice_label' => function (Customer $customer) {
                    return sprintf('%s %s %s (%s)', 
                        $customer->getName(),
                        $customer->getEmail()
                    );
                },
                'label' => 'Заказчик',
                'disabled' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
