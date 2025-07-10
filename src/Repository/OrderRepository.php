<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function findByIdAndCustomerId (?string $query): array
    {
        $qb = $this->createQueryBuilder('c')
        ->leftJoin('c.customer', 'customer');

        if ($query) {
            $qb->where('CAST(c.id AS CHAR) LIKE :q OR LOWER(customer.name) LIKE :q')
            ->setParameter('q', '%' . strtolower($query) . '%');
        }

        // dd($qb->getQuery()->getSQL());

        return $qb->getQuery()->getResult();
    }

        public function findLastMonthOrders(): array
    {
        $oneMonthAgo = new \DateTime('-1 month');

        return $this->createQueryBuilder('o')
            ->where('o.date >= :oneMonthAgo')
            ->setParameter('oneMonthAgo', $oneMonthAgo)
            ->orderBy('o.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

}