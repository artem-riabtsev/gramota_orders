<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function findByFilters(array $filters)
    {
        $qb = $this->createQueryBuilder('o');

        if (isset($filters['id'])) {
            $qb->andWhere('o.id = :id')
                ->setParameter('id', (int)$filters['id']);
        }

        if (isset($filters['date_from'])) {
            $qb->andWhere('o.date >= :date_from')
                ->setParameter('date_from', new \DateTime($filters['date_from']));
        }

        if (isset($filters['date_to'])) {
            $qb->andWhere('o.date <= :date_to')
                ->setParameter('date_to', new \DateTime($filters['date_to']));
        }

        if (isset($filters['amount_min'])) {
            $qb->andWhere('o.amount >= :amount_min')
                ->setParameter('amount_min', (float)$filters['amount_min']);
        }

        if (isset($filters['amount_max'])) {
            $qb->andWhere('o.amount <= :amount_max')
                ->setParameter('amount_max', (float)$filters['amount_max']);
        }

        if (isset($filters['payment_date_from'])) {
            $qb->andWhere('o.payment_date >= :payment_date_from')
                ->setParameter('payment_date_from', new \DateTime($filters['payment_date_from']));
        }

        if (isset($filters['payment_date_to'])) {
            $qb->andWhere('o.payment_date <= :payment_date_to')
                ->setParameter('payment_date_to', new \DateTime($filters['payment_date_to']));
        }

        if (isset($filters['payment_amount_min'])) {
            $qb->andWhere('o.payment_amount >= :payment_amount_min')
                ->setParameter('payment_amount_min', (float)$filters['payment_amount_min']);
        }

        if (isset($filters['payment_amount_max'])) {
            $qb->andWhere('o.payment_amount <= :payment_amount_max')
                ->setParameter('payment_amount_max', (float)$filters['payment_amount_max']);
        }

        if (isset($filters['customer_id'])) {
            $qb->andWhere('o.customer = :customer_id')
                ->setParameter('customer_id', (int)$filters['customer_id']);
        }

        if (isset($filters['status'])) {
            $qb->andWhere('o.status = :status')
                ->setParameter('status', (int)$filters['status']);
        }

        $qb->orderBy('o.id', 'DESC');

        return $qb->getQuery()->getResult();
    }


}
