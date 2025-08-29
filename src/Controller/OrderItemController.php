<?php

namespace App\Controller;

use App\Form\OrderItemForm;
use App\Repository\OrderRepository;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/orderitem')]
final class OrderItemController extends AbstractController
{
    #[Route('/new', name: 'app_orderItem_new', methods: ['GET', 'POST'])]
    public function new(Request $request, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        $orderId = $request->query->get('order');
        $order = $orderRepository->find($orderId);
        $orderItem = new OrderItem();
        $orderItem->setOrder($order);

        $form = $this->createForm(OrderItemForm::class, $orderItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order->addOrderItem($orderItem);
            $entityManager->flush();
            return $this->redirectToRoute('app_order_show', ['id' => $orderId]);
        }

        return $this->render('order_item/new.html.twig', [
            'form' => $form,
            'orderItem' => $orderItem,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_orderItem_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, OrderItem $orderItem, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OrderItemForm::class, $orderItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_order_show', ['id' => $orderItem->getOrder()->getId()]);
        }

        return $this->render('order_item/edit.html.twig', [
            'form' => $form,
            'orderItem' => $orderItem,
        ]);
    }

    #[Route('/{id}', name: 'app_orderItem_delete', methods: ['POST'])]
    public function delete(Request $request, OrderItem $orderItem, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $orderItem->getId(), $request->request->get('_token'))) {
            $orderItem->getOrder()->removeOrderItem($orderItem);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_order_show', ['id' => $orderItem->getOrder()->getId()], Response::HTTP_SEE_OTHER);
    }
}
