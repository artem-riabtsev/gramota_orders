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

    public function findOrders(?string $query): array
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->leftJoin('c.customer', 'customer');

        if (!empty($query)) {
            $qb->where('customer.name LIKE :q')
                ->orWhere('c.id = :id')
                ->setParameter('q', '%' . $query . '%')
                ->setParameter('id', $query);
        } else {
            $oneMonthAgo = new \DateTime('-1 month');
            $qb->where('c.date >= :oneMonthAgo')
                ->setParameter('oneMonthAgo', $oneMonthAgo);
        }

        return $qb
            ->orderBy('c.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findById(?string $query): array
    {
        return $this->createQueryBuilder('o')
            ->where('CAST(o.id AS CHAR) LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->orderBy('o.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findLastMonthOrders(): array
    {
        $oneMonthAgo = new \DateTime('-1 month');

        return $this->createQueryBuilder('o')
            ->where('o.date >= :oneMonthAgo')
            ->setParameter('oneMonthAgo', $oneMonthAgo)
            ->orderBy('o.date', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }
}
