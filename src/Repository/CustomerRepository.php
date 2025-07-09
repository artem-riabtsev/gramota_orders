<?php

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Customer>
 */
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function findByNameOrEmail(?string $query): array
    {
        $qb = $this->createQueryBuilder('c');

        if ($query) {
            $qb->where('LOWER(c.name) LIKE :q')
            ->orWhere('LOWER(c.email) LIKE :q')
            ->setParameter('q', '%' . strtolower($query) . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
