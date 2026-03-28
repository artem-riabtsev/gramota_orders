<?php

namespace App\Controller\Api;

use App\Entity\Order;
use App\Repository\CustomerRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/order')]
class OrderApiController extends AbstractController
{
    #[Route('/create', name: 'api_order_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, CustomerRepository $customerRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['customerId'])) {
            return $this->json(['success' => false, 'error' => 'Не выбран заказчик'], 400);
        }

        $customer = $customerRepository->find($data['customerId']);

        if (!$customer) {
            return $this->json(['success' => false, 'error' => 'Заказчик не найден'], 404);
        }

        $order = new Order();
        $order->setCustomer($customer);

        $em->persist($order);
        $em->flush();

        return $this->json([
            'success' => true,
            'orderId' => $order->getId()
        ]);
    }

    #[Route('/search', name: 'api_order_search', methods: ['GET'])]
    public function search(Request $request, OrderRepository $orderRepository): JsonResponse
    {
        $query = $request->query->get('q', '');

        if (strlen($query) < 2) {
            return $this->json([]);
        }

        $orders = $orderRepository->findOrders($query);

        $data = array_map(function (Order $order) {
            return [
                'id' => $order->getId(),
                'date' => $order->getDate()->format('d.m.Y'),
                'customer' => [
                    'id' => $order->getCustomer()->getId(),
                    'name' => $order->getCustomer()->getName()
                ],
                'orderTotal' => $order->getOrderTotal()->getAmount(),
                'status' => [
                    'value' => $order->getStatus()->value,
                    'label' => $order->getStatus()->label(),
                    'color' => $order->getStatus()->color()
                ]
            ];
        }, $orders);

        return $this->json($data);
    }

    #[Route('/list', name: 'api_order_list', methods: ['GET'])]
    public function list(Request $request, OrderRepository $orderRepository): JsonResponse
    {
        $query = $request->query->get('q', '');

        if (empty($query)) {
            $orders = $orderRepository->findLastMonthOrders();
        } else {
            $orders = $orderRepository->findOrders($query);
        }

        return $this->json(array_map(fn($o) => [
            'id' => $o->getId(),
            'date' => $o->getDate()->format('d.m.Y'),
            'customer' => [
                'id' => $o->getCustomer()->getId(),
                'name' => $o->getCustomer()->getName()
            ],
            'orderTotal' => $o->getOrderTotal()->getAmount(),
            'totalPaid' => $o->getTotalPaid()->getAmount(),
            'status' => [
                'label' => $o->getStatus()->label(),
                'color' => $o->getStatus()->color()
            ]
        ], $orders));
    }
}
