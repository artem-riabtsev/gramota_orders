<?php

namespace App\Repository;

use App\Entity\Price;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Price>
 */
class PriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Price::class);
    }

    public function findByFilters(array $filters)
    {
        $qb = $this->createQueryBuilder('o');

        if (isset($filters['id'])) {
            $qb->andWhere('o.id = :id')
                ->setParameter('id', (int)$filters['id']);
        }

        if (isset($filters['name'])) {
            $qb->andWhere('o.name = :name')
                ->setParameter('name', (string)$filters['name']);
        }

        if (isset($filters['price'])) {
            $qb->andWhere('o.price = :price')
                ->setParameter('price', (string)$filters['price']);
        }

        $qb->orderBy('o.id', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
