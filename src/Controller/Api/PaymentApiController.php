<?php

namespace App\Controller\Api;

use App\Repository\PaymentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/payment')]
class PaymentApiController extends AbstractController
{
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
}
