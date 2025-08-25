<?php

namespace App\Form;

use App\Entity\Price;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class PriceForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', null, ['label' => 'Описание'])
            ->add('price', NumberType::class, [
                'label' => 'Цена',
                'scale' => 2,
                'html5' => true,
                'attr' => [
                    'step' => '0.01',
                    'min' => '0',
                ],
                'constraints' => [
                    new PositiveOrZero(['message' => 'Цена не может быть отрицательной']),
                ],
            ])
            ->add('price1', AppMoneyType::class, [
                'label' => 'Цена123',
            ])
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'description',
                'placeholder' => 'Выберете продукт',
                'label' => 'Продукт',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->where('p.basic = true');
                }
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Price::class,
        ]);
    }
}
