<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderForm;
use App\Repository\OrderRepository;
use App\Repository\PriceRepository;
use App\Entity\Cart;
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

        if ($request->isMethod('POST')) {
            // Получаем текущие позиции заказа
            $currentCartItems = $order->getCart()->toArray();
            $deletedItems = $request->request->all('deleted_items') ?? [];
            $totalAmount = 0;

            // Обработка удаленных элементов
            foreach ($deletedItems as $id) {
                $cartItem = $entityManager->getRepository(Cart::class)->find($id);
                if ($cartItem && $cartItem->getOrder() === $order) {
                    $entityManager->remove($cartItem);
                    // Удаляем из массива текущих позиций
                    $currentCartItems = array_filter($currentCartItems, fn($item) => $item->getId() != $id);
                }
            }

            // Считаем сумму для неудаленных позиций
            foreach ($currentCartItems as $cartItem) {
                $totalAmount += $cartItem->getTotalAmount();
            }

            // Обработка новых/измененных позиций
            $cartData = $request->request->all('cart') ?? [];
            foreach ($cartData as $key => $item) {
                if (empty($item['product_id'])) continue;

                $priceEntity = $priceRepository->find($item['product_id']);
                if (!$priceEntity) continue;

                if (str_starts_with($key, 'new_')) {
                    $cartItem = new Cart();
                    $cartItem->setOrder($order);
                } else {
                    $cartItem = $entityManager->getRepository(Cart::class)->find($key);
                    if (!$cartItem) {
                        $cartItem = new Cart();
                        $cartItem->setOrder($order);
                    }
                }

                $quantity = max(1, (int)($item['quantity'] ?? 1));
                $price = str_replace(',', '.', $item['price'] ?? $priceEntity->getPrice());
                $ItemTotalOld = $cartItem->getTotalAmount();
                $ItemTotalActual = bcmul($price, $quantity, 2);

                $cartItem
                    ->setProduct($priceEntity)
                    ->setPrice($price)
                    ->setQuantity($quantity)
                    ->setTotalAmount($ItemTotalActual);

                $entityManager->persist($cartItem);
                $totalAmount = bcadd(
                    bcsub($totalAmount, $ItemTotalOld, 2),
                    $cartItem->getTotalAmount(),
                    2
                );
            }

            // Если корзина пуста - сумма 0
            if (empty($currentCartItems) && empty($cartData)) {
                $totalAmount = '0';
            }

            $order->setAmount($totalAmount);
            $payment_amount = $order->getPaymentAmount();
            if (bccomp($payment_amount, '0', 2) === 0 && bccomp($payment_amount, $totalAmount, 2) === -1) {
                $order->setStatus(1); // не оплачен
            } elseif (bccomp($payment_amount, '0', 2) === 1 && bccomp($payment_amount, $totalAmount, 2) === -1) {
                $order->setStatus(2); // частично оплачен
            } elseif (bccomp($payment_amount, $totalAmount, 2) === 0) {
                $order->setStatus(4); // оплачен
            } elseif (bccomp($payment_amount, $totalAmount, 2) === 1) {
                $order->setStatus(3); // переплата
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_order_edit', ['id' => $order->getId()]);
        }

        return $this->render('order/edit.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
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
