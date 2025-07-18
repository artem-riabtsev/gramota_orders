<?php

namespace App\Repository;

use App\Entity\Payment;
use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Payment>
 */
class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    public function recalculateOrderPaymentAmount(Order $order): void
    {
    $em = $this->getEntityManager();

    $lineTotal = (float) $this->createQueryBuilder('p')
        ->select('SUM(p.amount)')
        ->where('p.order = :order')
        ->setParameter('order', $order)
        ->getQuery()
        ->getSingleScalarResult();

    $order->setTotalPaid($lineTotal);

    $em->flush();
    }

    public function updateOrderPaymentAmount(int $paymentId, float $amountUpdated): void
    {
        $em = $this->getEntityManager();

        $payment = $em->getRepository(Payment::class)->find($paymentId);

        if (!$payment) {
            throw new \RuntimeException("Payment not found: ID $paymentId");
        }

        $order = $payment->getOrder();

        if (!$order) {
            throw new \RuntimeException("Order not found for payment ID $paymentId");
        }

        $amountOld = $payment->getAmount();

        $lineTotal = (float) $this->createQueryBuilder('p')
            ->select('SUM(p.amount)')
            ->where('IDENTITY(p.order) = :order_id')
            ->setParameter('order_id', $order)
            ->getQuery()
            ->getSingleScalarResult();

        $correctedAmount = $lineTotal - $amountOld + $amountUpdated;

        $order->setTotalPaid($correctedAmount);

        $em->flush();
    }

    public function findByOrderId(?string $query): array
    {
        $qb = $this->createQueryBuilder('c');

        if ($query) {
            $qb->where('IDENTITY(c.order) LIKE :q')
            ->setParameter('q', '%' . $query . '%');
        }

        return $qb->getQuery()->getResult();
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
