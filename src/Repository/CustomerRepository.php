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

    public function findCustomers(?string $query = null): array
    {
        if (empty($query)) {
            return $this->findAll();
        }

        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.name', 'ASC');

        $qb->where('c.name LIKE :q')
            ->orWhere('c.email LIKE :q')
            ->orWhere('c.phone LIKE :q')
            ->setParameter('q', '%' . $query . '%');

        return $qb->getQuery()->getResult();
    }
}
