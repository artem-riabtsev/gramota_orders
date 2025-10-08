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

    public function findPrice(?string $query): array
    {
        $qb = $this->createQueryBuilder('p');

        if ($query) {
            $qb->where('LOWER(p.description) LIKE :q')
                ->setParameter('q', '%' . strtolower($query) . '%');
        }
        $qb->orderBy('p.description', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function listPrices(?string $query = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.description', 'ASC');

        if ($query && $query !== '') {
            $qb->where('c.description LIKE :q')
                ->setParameter('q', '%' . $query . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
