<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderForm;
use App\Form\OrderNewForm;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CustomerRepository;
use App\Config\OrderStatus;

final class OrderController extends AbstractController
{

    #[Route(['/order', '/'], name: 'app_order_index', methods: ['GET'])]
    public function index(
        Request $request,
        OrderRepository $orderRepository,
    ): Response {

        $query = $request->query->get('q') ?? '';
        return $this->render('order/index.html.twig', [
            'orders' => $orderRepository->findOrders($query),
            'query' => $query,
        ]);
    }

    #[Route('/order/new', name: 'app_order_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, CustomerRepository $customerRepository): Response
    {

        $order = new Order();
        $form = $this->createForm(OrderNewForm::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($order);
            $entityManager->flush();

            return $this->redirectToRoute('app_order_show', ['id' => $order->getId()]);
        }

        $searchQuery = $request->query->get('q') ?? '';
        return $this->render('order/new.html.twig', [
            'customers' => $customerRepository->findByNameOrEmail($searchQuery),
            'searchQuery' => $searchQuery,
            'form' => $form
        ]);
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

    #[Route('/order/{id}/edit', name: 'app_order_edit', methods: ['GET', 'POST'])]
    public function editDate(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OrderForm::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_order_show', ['id' => $order->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('order/edit.html.twig', [
            'form' => $form,
            'order' => $order
        ]);
    }

    #[Route('/order/{id}/show', name: 'app_order_show', methods: ['GET'])]
    public function edit(Order $order): Response
    {

        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/order/{id}', name: 'app_order_delete', methods: ['POST'])]
    public function delete(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $order->getId(), $request->getPayload()->getString('_token'))) {
            if ($order->hasPayments()) {
                $this->addFlash('error', 'Нельзя удалить заказ, у которого есть платежи.');
                return $this->redirectToRoute('app_order_index');
            }
            $entityManager->remove($order);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
    }
}
