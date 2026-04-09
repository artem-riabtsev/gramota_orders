<?php

namespace App\Controller\Api;

use App\Entity\Order;
use App\Entity\OrderItem;
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

    public function __construct(
        private EntityManagerInterface $em
    ) {}

    #[Route('/list', name: 'api_order_list', methods: ['GET'])]
    public function list(Request $request, OrderRepository $orderRepository): JsonResponse
    {
        $query = $request->query->get('q', '');
        $page = (int)$request->query->get('page', 1);
        $limit = (int)$request->query->get('limit', 10);
        $offset = ($page - 1) * $limit;

        if (empty($query)) {
            $orders = $orderRepository->findBy([], ['date' => 'DESC'], $limit, $offset);
            $total = $orderRepository->count([]);
        } else {
            $orders = $orderRepository->findOrdersWithPagination($query, $limit, $offset);
            $total = $orderRepository->countOrdersBySearch($query);
        }

        $data = array_map(function (Order $order) {
            return [
                'id' => $order->getId(),
                'date' => $order->getDate()->format('d.m.Y'),
                'customer' => [
                    'id' => $order->getCustomer()->getId(),
                    'name' => $order->getCustomer()->getName()
                ],
                'orderTotal' => $order->getOrderTotal()->getAmount(),
                'totalPaid' => $order->getTotalPaid()->getAmount(),
                'status' => [
                    'value' => $order->getStatus()->value,
                    'label' => $order->getStatus()->label(),
                    'color' => $order->getStatus()->color()
                ]
            ];
        }, $orders);

        return $this->json([
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ]);
    }

    #[Route('/{id}', name: 'api_order_get', methods: ['GET'])]
    public function getOrder(Order $order): JsonResponse
    {
        $items = array_map(function (OrderItem $item) {
            return [
                'id' => $item->getId(),
                'description' => $item->getDescription(),
                'product' => [
                    'id' => $item->getProduct()->getId(),
                    'description' => $item->getProduct()->getDescription()
                ],
                'quantity' => $item->getQuantity(),
                'price' => (float) $item->getPrice()->getAmount()->toFloat(),
                'lineTotal' => (float) $item->getLineTotal()->getAmount()->toFloat(),
            ];
        }, $order->getOrderItems());

        $data = [
            'id' => $order->getId(),
            'date' => $order->getDate()->format('d.m.Y'),
            'customer' => [
                'id' => $order->getCustomer()->getId(),
                'name' => $order->getCustomer()->getName(),
                'email' => $order->getCustomer()->getEmail(),
                'phone' => $order->getCustomer()->getPhone()
            ],
            'orderTotal' => (float) $order->getOrderTotal()->getAmount()->toFloat(),
            'totalPaid' => (float) $order->getTotalPaid()->getAmount()->toFloat(),
            'status' => [
                'value' => $order->getStatus()->value,
                'label' => $order->getStatus()->label(),
                'color' => $order->getStatus()->color()
            ],
            'items' => $items
        ];

        return $this->json($data);
    }

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
                'totalPaid' => $order->getTotalPaid()->getAmount(),
                'status' => [
                    'value' => $order->getStatus()->value,
                    'label' => $order->getStatus()->label(),
                    'color' => $order->getStatus()->color()
                ]
            ];
        }, $orders);

        return $this->json($data);
    }

    #[Route('/{id}/date', name: 'api_order_update_date', methods: ['PUT'])]
    public function updateDate(Order $order, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['date'])) {
            $order->setDate(new \DateTimeImmutable($data['date']));
            $this->em->flush();
            return $this->json(['success' => true]);
        }

        return $this->json(['success' => false, 'error' => 'Дата не указана'], 400);
    }

    #[Route('/{id}/delete', name: 'api_order_delete', methods: ['DELETE'])]
    public function delete(Order $order): JsonResponse
    {
        if ($order->hasPayments()) {
            return $this->json(['success' => false, 'error' => 'Нельзя удалить заказ с платежами'], 400);
        }
        $this->em->remove($order);
        $this->em->flush();
        return $this->json(['success' => true]);
    }
}
