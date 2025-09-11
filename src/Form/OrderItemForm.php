<?php

namespace App\Form;

use App\Entity\OrderItem;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Repository\ProductRepository;
use App\AppBundle\Form\AppMoneyType;

class OrderItemForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $projectId = $options['data']?->getProduct()?->getProject()?->getId();

        $builder
            ->add('description', TextType::class, [
                'label' => 'Название позиции',
            ])
            ->add('product', EntityType::class, [
                'label' => 'Продукт',
                'class' => Product::class,
                'choice_label' => 'description',
                'placeholder' => $projectId ? false : 'Выберите продукт',
                'query_builder' => function (ProductRepository $repo) use ($projectId) {
                    $qb = $repo->createQueryBuilder('p')
                        ->orderBy('p.description', 'ASC');
                    if ($projectId) {
                        $qb->where('p.project = :projectId')
                            ->setParameter('projectId', $projectId);
                    }
                    return $qb;
                }
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'Количество',
                'attr' => [
                    'min' => 1,
                ]
            ])
            ->add('price', AppMoneyType::class, [
                'label' => 'Цена',
            ])
            ->add('line_total', AppMoneyType::class, [
                'label' => 'Всего',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderItem::class,
        ]);
    }
}
