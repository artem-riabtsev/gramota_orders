<?php

namespace App\Repository;

use App\Entity\Price;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Price::class);
    }

    public function findByDescriptionOrderByDescription(?string $query): array
    {
        $qb = $this->createQueryBuilder('p');

        if ($query) {
            $qb->where('LOWER(p.description) LIKE :q')
                ->setParameter('q', '%' . strtolower($query) . '%');
        }
        $qb->orderBy('p.description', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
