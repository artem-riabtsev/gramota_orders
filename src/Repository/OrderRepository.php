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

    public function findOrdersWithPagination(?string $query, int $limit, int $offset): array
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.customer', 'customer')
            ->orderBy('o.date', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if (!empty($query)) {
            $qb->where('customer.name LIKE :q')
                ->orWhere('o.id LIKE :id')
                ->setParameter('q', '%' . $query . '%')
                ->setParameter('id', '%' . $query . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function countOrdersBySearch(?string $query): int
    {
        $qb = $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->leftJoin('o.customer', 'customer');

        if (!empty($query)) {
            $qb->where('customer.name LIKE :q')
                ->orWhere('o.id = :id')
                ->setParameter('q', '%' . $query . '%')
                ->setParameter('id', $query);
        }

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    // public function findOrders(?string $query): array
    // {
    //     $qb = $this
    //         ->createQueryBuilder('c')
    //         ->leftJoin('c.customer', 'customer');

    //     if (!empty($query)) {
    //         $qb->where('customer.name LIKE :q')
    //             ->orWhere('c.id = :id')
    //             ->setParameter('q', '%' . $query . '%')
    //             ->setParameter('id', $query);
    //     } else {
    //         $oneMonthAgo = new \DateTime('-1 month');
    //         $qb->where('c.date >= :oneMonthAgo')
    //             ->setParameter('oneMonthAgo', $oneMonthAgo);
    //     }

    //     return $qb
    //         ->orderBy('c.date', 'DESC')
    //         ->getQuery()
    //         ->getResult();
    // }

    // public function findById(?string $query): array
    // {
    //     return $this->createQueryBuilder('o')
    //         ->where('CAST(o.id AS CHAR) LIKE :q')
    //         ->setParameter('q', '%' . $query . '%')
    //         ->orderBy('o.id', 'ASC')
    //         ->getQuery()
    //         ->getResult();
    // }

    // public function findLastMonthOrders(): array
    // {
    //     $oneMonthAgo = new \DateTime('-1 month');

    //     return $this->createQueryBuilder('o')
    //         ->leftJoin('o.customer', 'c')
    //         ->where('o.date >= :oneMonthAgo')
    //         ->setParameter('oneMonthAgo', $oneMonthAgo)
    //         ->orderBy('o.date', 'DESC')
    //         ->getQuery()
    //         ->getResult();
    // }
}
