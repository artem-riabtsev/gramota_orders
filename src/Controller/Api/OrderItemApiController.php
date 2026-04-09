<?php

namespace App\Controller\Api;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\OrderRepository;
use App\Repository\PriceRepository;
use App\Repository\ProductRepository;
use Brick\Money\Money;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/order-item')]
class OrderItemApiController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    #[Route('/create', name: 'api_order_item_create', methods: ['POST'])]
    public function create(
        Request $request,
        OrderRepository $orderRepository,
        PriceRepository $priceRepository,
        ProductRepository $productRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['orderId'])) {
            return $this->json(['success' => false, 'error' => 'Не указан заказ'], 400);
        }

        $order = $orderRepository->find($data['orderId']);
        if (!$order) {
            return $this->json(['success' => false, 'error' => 'Заказ не найден'], 404);
        }

        $orderItem = new OrderItem();
        $orderItem->setOrder($order);

        // Если выбрана позиция из прайса - берем данные из нее
        if (isset($data['priceId']) && $data['priceId']) {
            $price = $priceRepository->find($data['priceId']);
            if ($price) {
                $orderItem->setDescription($price->getDescription());
                $orderItem->setProduct($price->getProduct());
                $orderItem->setPrice($price->getPrice());
            }
        }

        // Ручной ввод (перезаписывает данные из прайса если нужно)
        if (isset($data['description']) && $data['description']) {
            $orderItem->setDescription($data['description']);
        }

        if (isset($data['productId']) && $data['productId']) {
            $product = $productRepository->find($data['productId']);
            if ($product) {
                $orderItem->setProduct($product);
            }
        }

        if (isset($data['quantity'])) {
            $orderItem->setQuantity((int)$data['quantity']);
        }

        if (isset($data['price'])) {
            $orderItem->setPrice(Money::of((float)$data['price'], 'RUB'));
        }

        // Рассчитываем итоговую сумму
        $lineTotal = $orderItem->getPrice()->multipliedBy($orderItem->getQuantity());
        $orderItem->setLineTotal($lineTotal);

        $order->addOrderItem($orderItem);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'item' => [
                'id' => $orderItem->getId(),
                'description' => $orderItem->getDescription(),
                'product' => [
                    'id' => $orderItem->getProduct()->getId(),
                    'description' => $orderItem->getProduct()->getDescription()
                ],
                'quantity' => $orderItem->getQuantity(),
                'price' => $orderItem->getPrice()->getAmount(),
                'lineTotal' => $orderItem->getLineTotal()->getAmount()
            ]
        ]);
    }

    #[Route('/{id}', name: 'api_order_item_update', methods: ['PUT'])]
    public function update(OrderItem $orderItem, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['quantity'])) {
            $orderItem->setQuantity((int)$data['quantity']);
        }

        if (isset($data['price'])) {
            $orderItem->setPrice(Money::of((float)$data['price'], 'RUB'));
        }

        if (isset($data['description'])) {
            $orderItem->setDescription($data['description']);
        }

        // Пересчитываем сумму
        $lineTotal = $orderItem->getPrice()->multipliedBy($orderItem->getQuantity());
        $orderItem->setLineTotal($lineTotal);

        $this->em->flush();

        return $this->json([
            'success' => true,
            'item' => [
                'id' => $orderItem->getId(),
                'quantity' => $orderItem->getQuantity(),
                'price' => $orderItem->getPrice()->getAmount(),
                'lineTotal' => $orderItem->getLineTotal()->getAmount(),
                'description' => $orderItem->getDescription()
            ]
        ]);
    }

    #[Route('/{id}', name: 'api_order_item_delete', methods: ['DELETE'])]
    public function delete(OrderItem $orderItem): JsonResponse
    {
        $order = $orderItem->getOrder();
        $order->removeOrderItem($orderItem);
        $this->em->flush();

        return $this->json(['success' => true]);
    }

    // #[Route('/{id}', name: 'api_order_get', methods: ['GET'])]
    // public function getOrder(Order $order): JsonResponse
    // {
    //     $items = array_map(function (OrderItem $item) {
    //         return [
    //             'id' => $item->getId(),
    //             'description' => $item->getDescription(),
    //             'product' => [
    //                 'id' => $item->getProduct()->getId(),
    //                 'description' => $item->getProduct()->getDescription()
    //             ],
    //             'quantity' => $item->getQuantity(),
    //             'price' => $item->getPrice()->getAmount(),
    //             'lineTotal' => $item->getLineTotal()->getAmount()
    //         ];
    //     }, $order->getOrderItems());

    //     $data = [
    //         'id' => $order->getId(),
    //         'date' => $order->getDate()->format('d.m.Y'),
    //         'customer' => [
    //             'id' => $order->getCustomer()->getId(),
    //             'name' => $order->getCustomer()->getName(),
    //             'email' => $order->getCustomer()->getEmail(),
    //             'phone' => $order->getCustomer()->getPhone()
    //         ],
    //         'orderTotal' => $order->getOrderTotal()->getAmount(),
    //         'totalPaid' => $order->getTotalPaid()->getAmount(),
    //         'status' => [
    //             'value' => $order->getStatus()->value,
    //             'label' => $order->getStatus()->label(),
    //             'color' => $order->getStatus()->color()
    //         ],
    //         'items' => $items
    //     ];

    //     return $this->json($data);
    // }
}
