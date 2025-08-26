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

    public function findByNameOrderByDateByDescription(string $query): array
    {
        $qb = $this->createQueryBuilder('c');

        if (!empty($query)) {
            $qb->where('c.description LIKE :q')
                ->setParameter('q', "%$query%");
        }

        $qb->orderBy('c.date', 'DESC')
            ->addOrderBy('c.description', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
