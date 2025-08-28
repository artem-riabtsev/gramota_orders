<?php

namespace App\Controller;

use App\Entity\Price;
use App\Form\OrderItemTemplateForm;
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
use App\Config\OrderStatus;

#[Route('/orderitem')]
final class OrderItemController extends AbstractController
{
    #[Route('/new', name: 'app_orderItem_new', methods: ['GET', 'POST'])]
    public function new(Request $request, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        $orderId = $request->query->get('order_id');
        $order = $orderRepository->find($orderId);
        $orderItem = new OrderItem();
        $orderItem->setOrder($order);

        $form = $this->createForm(OrderItemForm::class, $orderItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($orderItem);
            $entityManager->flush();
            return $this->redirectToRoute('app_order_edit', ['id' => $orderId]);
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
            return $this->redirectToRoute('app_order_edit', ['id' => $orderItem->getOrder()->getId()]);
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
            $orderId = $request->request->get('order_id');
            $entityManager->remove($orderItem);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_order_edit', ['id' => $orderId], Response::HTTP_SEE_OTHER);
    }

    // #[Route('/order/{id}/new-orderitem', name: 'app_choose_template', methods: ['GET', 'POST'])]
    // public function chooseTemplate($id, Request $request, EntityManagerInterface $entityManager): Response
    // {

    //     $prices = $entityManager->getRepository(Price::class)->findAll();

    //     $priceChoices = [];
    //     foreach ($prices as $price) {
    //         $priceChoices[$price->getDescription()] = $price->getId();
    //     }

    //     $form = $this->createForm(OrderItemTemplateForm::class, null, [
    //         'price_choices' => $priceChoices
    //     ]);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $formData = $form->getData();
    //         $selectedPriceId = $formData['select_template'];

    //         $entityManager->flush();
    //         return $this->redirectToRoute('app_new_item', [
    //             'id' => $id,
    //             'price_template' => $selectedPriceId,
    //         ]);
    //     }

    //     return $this->render('order_item/choose_template.html.twig', [
    //         'form' => $form,
    //         'id' => $id,
    //     ]);
    // }
}
