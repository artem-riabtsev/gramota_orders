<?php

namespace App\Form;

use App\Entity\OrderItem;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProductRepository;

class OrderItemForm extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentProduct = ($options['data'] ?? null) instanceof OrderItem ? $options['data']->getProduct() : null;
        $currentProject = $currentProduct ? $currentProduct->getProject() : null;

        $builder
            ->add('description', ChoiceType::class, [
                'label' => 'Шаблон',
                'mapped' => false,
                'choices' => array_combine(
                    array_column($options['prices'], 'description'),
                    array_column($options['prices'], 'description')
                ),
                'choice_attr' => function ($choice, $key, $value) use ($options) {
                    $price = array_values(array_filter($options['prices'], function ($item) use ($value) {
                        return $item['description'] === $value;
                    }))[0] ?? null;

                    return [
                        'data-price' => $price['price'] ?? '',
                        'data-product-id' => $price['product']['id'] ?? '',
                        'data-product-name' => $price['product']['description'] ?? '',
                        'data-project-id' => $price['product']['project_id'] ?? null
                    ];
                },
                'placeholder' => 'Выберите шаблон',
                'attr' => [
                    'class' => 'form-select price-source mb-3',
                    'id' => 'order_item_form_description'
                ]
            ])
            ->add('description_text', TextType::class, [
                'label' => 'Название позиции',
                'attr' => [
                    'class' => 'form-control mb-3',
                    'id' => 'order_item_form_description_text'
                ]
            ])
            ->add('product', EntityType::class, [
                'label' => 'Продукт',
                'placeholder' => 'Выберите продукт',
                'class' => Product::class,
                'choice_label' => 'description',
                'choice_attr' => function (Product $product) {
                    return ['data-project-id' => $product->getProject() ? $product->getProject()->getId() : ''];
                },
                'attr' => [
                    'class' => 'form-select product-select mb-3',
                    'id' => 'order_item_form_product'
                ],
                'query_builder' => function (ProductRepository $repo) {
                    return $repo->createQueryBuilder('p')->orderBy('p.description', 'ASC');
                }
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'Количество',
                'attr' => [
                    'min' => 1,
                    'class' => 'form-control quantity mb-3',
                    'id' => 'order_item_form_quantity',
                ]
            ])
            ->add('price', TextType::class, [
                'label' => 'Цена',
                'attr' => [
                    'class' => 'form-control price mb-3',
                    'id' => 'order_item_form_price',
                ]
            ])
            ->add('line_total', TextType::class, [
                'label' => 'Всего',
                'attr' => [
                    'class' => 'form-control total mb-3',
                    'id' => 'order_item_form_line_total',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderItem::class,
            'prices' => [],
        ]);

        $resolver->setAllowedTypes('prices', 'array');
    }
}
