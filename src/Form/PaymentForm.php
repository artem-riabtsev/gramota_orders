<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\Payment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\AppBundle\Form\AppMoneyType;

class PaymentForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'label' => 'Дата',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control w-25',
                    'style' => 'border-radius: 8px; padding: 8px 12px;'
                ]
            ])
            ->add('amount', AppMoneyType::class, [
                'label' => 'Сумма платежа',
                'attr' => [
                    'class' => 'w-50'
                ]
            ])
            ->add('order', EntityType::class, [
                'class' => Order::class,
                'label' => 'Номер заказа',
                'choice_label' => 'id',
                'disabled' => true,
                'attr' => [
                    'class' => 'w-50'
                ]
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
