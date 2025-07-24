<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderForm;
use App\Form\OrderItemForm;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\PriceRepository;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CustomerRepository;

final class OrderController extends AbstractController
{
    #[Route('/', name: 'app_home_redirect', methods: ['GET'])]
    public function redirectToOrders(): Response
    {
        return $this->redirectToRoute('app_order_index');
    }

    #[Route('/order', name: 'app_order_index', methods: ['GET'])]
    public function index(
        Request $request,
        OrderRepository $orderRepository,
    ): Response {
        
        $query = $request->query->get('q');

        if ($query) {
            $orders = $orderRepository->findByIdAndCustomerId($query);
        } else {
            $orders = $orderRepository->findLastMonthOrders();
        }

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
            'query' => $query,
        ]);

    }

    #[Route('/order/new', name: 'app_order_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, CustomerRepository $customerRepository): Response
    {
        $order = new Order();

        $customerId = $request->query->get('customer');
        if ($customerId) {
            $customer = $customerRepository->find($customerId);
            if ($customer) {
                $order->setCustomer($customer);
            }
        }

        $entityManager->persist($order);
        $entityManager->flush();

        return $this->redirectToRoute('app_order_edit', ['id' => $order->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/order/select', name: 'app_order_select')]
    public function select(Request $request, OrderRepository $orderRepository): Response
    {
        $query = $request->query->get('q');

        if ($query) {
            $orders = $orderRepository->findById($query);
        } else {
            $orders = $orderRepository->findBy([], ['id' => 'ASC']);
        }

        return $this->render('order/select.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/order/{id}', name: 'app_order_show', methods: ['GET'])]
    public function show(Order $order): Response
    {
        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/order/{id}/edit', name: 'app_order_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Order $order,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository,
        PriceRepository $priceRepository
    ): Response {
        $form = $this->createForm(OrderForm::class, $order);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $request->request->has('order_date')) {
            $newDate = \DateTime::createFromFormat('Y-m-d', $request->request->get('order_date'));
            if ($newDate) {
                $order->setDate($newDate);
                $entityManager->flush();
                return $this->redirectToRoute('app_order_edit', ['id' => $order->getId()]);
            }
        }

        $prices = $priceRepository->createQueryBuilder('p')
            ->leftJoin('p.product', 'product')
            ->leftJoin('product.project', 'project')
            ->select('p.description', 'p.price', 
                'product.id as product_id', 
                'product.description as product_description',
                'project.id as project_id')
            ->getQuery()
            ->getResult();

        $priceChoices = [];
        foreach ($prices as $price) {
            $priceChoices[] = [
                'description' => $price['description'],
                'price' => $price['price'],
                'product' => [
                    'id' => $price['product_id'],
                    'description' => $price['product_description'],
                    'project_id' => $price['project_id']
                ]
            ];
        }

        $orderItemForm = $this->createForm(OrderItemForm::class, null, [
            'prices' => $priceChoices
        ]);

        if ($request->isMethod('POST')) {
            // Получаем текущие позиции заказа
            $currentOrderItems = $order->getOrderItem()->toArray();
            $deletedItems = $request->request->all('deleted_items') ?? [];
            $lineTotal = '0';

            // Обработка удаленных элементов
            foreach ($deletedItems as $id) {
                $orderItem = $entityManager->getRepository(OrderItem::class)->find($id);
                if ($orderItem && $orderItem->getOrder() === $order) {
                    $entityManager->remove($orderItem);
                    $currentOrderItems = array_filter($currentOrderItems, fn($item) => $item->getId() != $id);
                }
            }

            // Считаем сумму для неудаленных позиций
            foreach ($currentOrderItems as $orderItem) {
                $lineTotal = bcadd($lineTotal, (string)$orderItem->getLineTotal(), 2);
            }

            // Обработка новых/измененных позиций
            $orderItemData = $request->request->all('order_item') ?? [];

            foreach ($orderItemData as $key => $item) {
                if (empty($item['product_id'])) continue;

                $productEntity = $productRepository->find($item['product_id']);
                if (!$productEntity) continue;

                $priceEntity = $priceRepository->findOneBy(['description' => $item['description']]);
                $description = $priceEntity ? $priceEntity->getDescription() : $item['description'];

                if (str_starts_with($key, 'new')) {
                    $orderItem = new OrderItem();
                    $orderItem->setOrder($order);
                    $order->addOrderItem($orderItem);
                } else {
                    $orderItem = $entityManager->getRepository(OrderItem::class)->find($key);
                    if (!$orderItem) {
                        $orderItem = new OrderItem();
                        $orderItem->setOrder($order);
                        $order->addOrderItem($orderItem);
                    }
                }

                $quantity = max(1, (int)($item['quantity'] ?? 1));
                $price = str_replace(',', '.', $item['price'] ?? $priceEntity->getPrice());

                $ItemTotalOld = $orderItem->getLineTotal() ?? '0';
                $ItemTotalActual = bcmul($price, $quantity, 2);

                $orderItem
                    ->setProduct($productEntity)
                    ->setDescription($description)
                    ->setPrice($price)
                    ->setQuantity($quantity)
                    ->setLineTotal($ItemTotalActual);

                $entityManager->persist($orderItem);

                $lineTotal = bcadd(
                    bcsub($lineTotal, $ItemTotalOld, 2),
                    $ItemTotalActual,
                    2
                );
            }

            if (empty($currentOrderItems) && empty($orderItemData)) {
                $lineTotal = '0';
            }

            $order->setOrderTotal($lineTotal);
            $totalPaid = $order->getTotalPaid();

            if (bccomp($totalPaid, '0', 2) === 0 && bccomp($totalPaid, $lineTotal, 2) === -1) {
                $order->setStatus(1); // не оплачен
            } elseif (bccomp($totalPaid, '0', 2) === 1 && bccomp($totalPaid, $lineTotal, 2) === -1) {
                $order->setStatus(2); // частично оплачен
            } elseif (bccomp($totalPaid, $lineTotal, 2) === 0) {
                $order->setStatus(4); // оплачен
            } elseif (bccomp($totalPaid, $lineTotal, 2) === 1) {
                $order->setStatus(3); // переплата
            }

            $entityManager->flush();
            return $this->redirectToRoute('app_order_edit', ['id' => $order->getId()]);
        }

        return $this->render('order/edit.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
            'orderItemForm' => $orderItemForm->createView(),
            'products' => $productRepository->findAll(),
            'prices' => $priceRepository->findAll(),
        ]);
    }

    #[Route('/order/{id}', name: 'app_order_delete', methods: ['POST'])]
    public function delete(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->getPayload()->getString('_token'))) {

            if (count($order->getPayments()) > 0) {
            $this->addFlash('error', 'Нельзя удалить заказ, у которого есть платежи.');
            return $this->redirectToRoute('app_order_index');
        }

            $entityManager->remove($order);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
    }
}
