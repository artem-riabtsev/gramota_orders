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

    public function findByFilters(array $filters)
    {
        $qb = $this->createQueryBuilder('o');

        if (isset($filters['id'])) {
            $qb->andWhere('o.id = :id')
                ->setParameter('id', (int)$filters['id']);
        }

        if (isset($filters['surname'])) {
            $qb->andWhere('o.surname = :surname')
                ->setParameter('surname', (string)$filters['surname']);
        }

        if (isset($filters['name'])) {
            $qb->andWhere('o.name = :name')
                ->setParameter('name', (string)$filters['name']);
        }

        if (isset($filters['patronymic'])) {
            $qb->andWhere('o.patronymic = :patronymic')
                ->setParameter('patronymic', (string)$filters['patronymic']);
        }

        if (isset($filters['email'])) {
            $qb->andWhere('o.email = :email')
                ->setParameter('email', (string)$filters['email']);
        }

        if (isset($filters['phone'])) {
            $qb->andWhere('o.phone = :phone')
                ->setParameter('phone', (string)$filters['phone']);
        }

        $qb->orderBy('o.id', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
