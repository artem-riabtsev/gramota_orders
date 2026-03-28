<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderForm;
use App\Form\OrderNewForm;
use App\Form\OrderItemTemplateForm;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\PriceRepository;

final class OrderController extends AbstractController
{
    #[Route(['/order', '/'], name: 'app_order_index', methods: ['GET'])]
    public function index(Request $request, OrderRepository $orderRepository): Response
    {
        $query = $request->query->get('q') ?? '';
        return $this->render('order/index.html.twig', [
            'orders' => $orderRepository->findOrders($query),
            'query' => $query,
        ]);
    }

    #[Route('/order/new', name: 'app_order_new', methods: ['GET'])]
    public function newReact(): Response
    {
        return $this->render('order/new.html.twig');
    }

    #[Route('/order/{id}', name: 'app_order_show', methods: ['GET'])]
    public function show(Order $order): Response
    {
        return $this->render('order/show.html.twig', [
            'order' => $order,
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

    #[Route('/order/{id}/delete', name: 'app_order_delete', methods: ['POST'])]
    public function delete(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        $redirectUrl = $request->query->get('redirect') ?? $this->generateUrl('app_order_index');

        if ($this->isCsrfTokenValid('delete' . $order->getId(), $request->getPayload()->getString('_token'))) {
            if ($order->hasPayments()) {
                $this->addFlash('error', 'Нельзя удалить заказ, у которого есть платежи.');
                return $this->redirect($redirectUrl);
            }
            $entityManager->remove($order);
            $entityManager->flush();
        }

        return $this->redirect($redirectUrl);
    }

    #[Route('/order/{id}/new-orderitem', name: 'app_order_template')]
    public function selectTemplate(Order $order, Request $request, PriceRepository $priceRepository): Response
    {
        $form = $this->createForm(OrderItemTemplateForm::class);

        $searchQuery = $request->query->get('q') ?? '';
        return $this->render('order/new-orderitem.template.html.twig', [
            'form' => $form->createView(),
            'order' => $order,
            'searchQuery' => $searchQuery,
            'prices' => $priceRepository->listPrices($searchQuery),
        ]);
    }
}
