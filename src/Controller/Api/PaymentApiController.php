<?php

namespace App\Controller\Api;

use App\Entity\Payment;
use App\Repository\PaymentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/payment')]
class PaymentApiController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $em
    ) {}

    #[Route('/list', name: 'api_payment_list', methods: ['GET'])]
    public function list(Request $request, PaymentRepository $paymentRepository): JsonResponse
    {
        $query = $request->query->get('q', '');
        $page = (int)$request->query->get('page', 1);
        $limit = (int)$request->query->get('limit', 10);
        $offset = ($page - 1) * $limit;

        if (empty($query)) {
            $payments = $paymentRepository->findBy([], ['date' => 'DESC'], $limit, $offset);
            $total = $paymentRepository->count([]);
        } else {
            $payments = $paymentRepository->findPaymentsWithPagination($query, $limit, $offset);
            $total = $paymentRepository->countPaymentsBySearch($query);
        }

        $data = array_map(function ($payment) {
            return [
                'id' => $payment->getId(),
                'orderId' => $payment->getOrder()->getId(),
                'orderCustomer' => $payment->getOrder()->getCustomer()->getName(),
                'date' => $payment->getDate()->format('d.m.Y'),
                'amount' => $payment->getAmount()->getAmount()
            ];
        }, $payments);

        return $this->json([
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ]);
    }

    #[Route('/{id}/delete', name: 'api_payment_delete', methods: ['DELETE'])]
    public function delete(Payment $payment): JsonResponse
    {
        $order = $payment->getOrder();
        $order->removePayment($payment);
        $this->em->flush();
        return $this->json(['success' => true]);
    }
}
