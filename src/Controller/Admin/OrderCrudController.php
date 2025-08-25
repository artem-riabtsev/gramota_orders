<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Config\OrderStatus;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            ChoiceField::new('status')
                ->setChoices([
                    'Пустой' => OrderStatus::EMPTY,
                    'Не оплачен' => OrderStatus::UNPAID,
                    'Частично оплачен' => OrderStatus::PARTIALLY_PAID,
                    'Переплата' => OrderStatus::OVERPAID,
                    'Оплачен' => OrderStatus::PAID,
                ])
                ->renderAsNativeWidget(),
        ];
    }
}
