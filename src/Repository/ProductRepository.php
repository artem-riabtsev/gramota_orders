<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findProductWithPagination(string $query, int $limit, int $offset): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.description LIKE :q')
            ->setParameter('q', "%$query%")
            ->orderBy('p.date', 'DESC')
            ->addOrderBy('p.description', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    public function countProductBySearch(string $query): int
    {
        return (int)$this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.description LIKE :q')
            ->setParameter('q', "%$query%")
            ->getQuery()
            ->getSingleScalarResult();
    }

    // public function findProduct(string $query): array
    // {
    //     $qb = $this->createQueryBuilder('c');

    //     if (!empty($query)) {
    //         $qb->where('c.description LIKE :q')
    //             ->setParameter('q', "%$query%");
    //     }

    //     $qb->orderBy('c.date', 'DESC')
    //         ->addOrderBy('c.description', 'ASC');

    //     return $qb->getQuery()->getResult();
    // }
}
