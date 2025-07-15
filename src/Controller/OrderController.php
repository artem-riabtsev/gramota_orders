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

        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->request->has('deleted_items') && !empty($request->request->get('deleted_items'))) {
                $deletedIds = array_filter(explode(',', $request->request->get('deleted_items')));
                foreach ($deletedIds as $id) {
                    $cartItem = $entityManager->getRepository(Cart::class)->find($id);
                    if ($cartItem && $cartItem->getOrder() === $order) {
                        $entityManager->remove($cartItem);
                        $order->getCart()->removeElement($cartItem);
                    }
                }
            }

            $totalAmount = 0;
            $cartData = $request->request->all('cart', []);

            foreach ($cartData as $key => $item) {
                if (empty($item['product_id'])) continue;

                $priceEntity = $priceRepository->find($item['product_id']);
                if (!$priceEntity) continue;

                if (str_starts_with($key, 'new_')) {
                    $cartItem = new Cart();
                    $cartItem->setOrder($order);
                } else {
                    $cartItem = $order->getCart()->filter(fn($c) => $c->getId() == $key)->first() ?? new Cart();
                    $cartItem->setOrder($order);
                }

                $quantity = max(1, (int)($item['quantity'] ?? 1));
                $price = (float)str_replace(',', '.', $item['price'] ?? $priceEntity->getPrice());

                $cartItem
                    ->setProduct($priceEntity)
                    ->setPrice($price)
                    ->setQuantity($quantity)
                    ->setTotalAmount($price * $quantity);

                $entityManager->persist($cartItem);
                $totalAmount += $cartItem->getTotalAmount();
            }

            if (empty($cartData)) {
                $totalAmount = 0;
                foreach ($order->getCart() as $cartItem) {
                    $entityManager->remove($cartItem);
                }
                $order->getCart()->clear();
            }

            $order->setAmount($totalAmount);
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

    #[Route('/order/{id}/complete', name: 'app_order_complete', methods: ['POST'])]
    public function complete(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('complete'.$order->getId(), $request->getPayload()->getString('_token'))) {
            if (count($order->getPayments()) === 0) {
            $this->addFlash('error', 'Заказ не содержит платежей!');
    
        } elseif ($order->getAmount() !== $order->getPaymentAmount()) {
            $this->addFlash('error', 'Сумма оплаты не соответствует сумме заказа!');
        
        } elseif ($order->getStatus() === 0) {
            $order->setStatus(1);
            $entityManager->flush();
        }
        }

        $q = $request->request->get('q');
        $routeParams = $q ? ['q' => $q] : [];

        return $this->redirectToRoute('app_order_index', $routeParams, Response::HTTP_SEE_OTHER);
    }
}
