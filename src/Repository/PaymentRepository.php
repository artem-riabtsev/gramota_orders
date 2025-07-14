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

    $totalAmount = (float) $this->createQueryBuilder('p')
        ->select('SUM(p.amount)')
        ->where('p.order = :order')
        ->setParameter('order', $order)
        ->getQuery()
        ->getSingleScalarResult();

    $order->setPaymentAmount($totalAmount);

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

        // $orderId = $order->getId();
        $amountOld = $payment->getAmount();

        $totalAmount = (float) $this->createQueryBuilder('p')
            ->select('SUM(p.amount)')
            ->where('IDENTITY(p.order) = :order_id')
            ->setParameter('order_id', $order)
            ->getQuery()
            ->getSingleScalarResult();

        $correctedAmount = $totalAmount - $amountOld + $amountUpdated;

        $order->setPaymentAmount($correctedAmount);

        $em->flush();
    }

}
