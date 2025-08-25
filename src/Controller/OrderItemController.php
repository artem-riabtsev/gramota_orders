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

final class OrderItemController extends AbstractController
{
    #[Route('/order/orderitem/new/{id}', name: 'app_new_item', methods: ['GET', 'POST'])]
    public function newItem($id, Request $request, PriceRepository $priceRepository, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        $priceId = $request->query->get('price_template');

        $priceData = $priceRepository->find($priceId);
        $order = $orderRepository->find($id);

        $orderItem = new OrderItem();
        $orderItem->setDescription($priceData->getDescription());
        $orderItem->setPrice($priceData->getPrice());
        $orderItem->setProduct($priceData->getProduct());
        $orderItem->setOrder($order);

        $form = $this->createForm(OrderItemForm::class, $orderItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_order_index');
        }

        return $this->render('order_item/item.html.twig', [
            'form' => $form,
            'orderItem' => $orderItem,
            'id' => $id,
            'price_template' => $priceId,
        ]);
    }

    #[Route('/order/{id}/new-orderitem', name: 'app_choose_template', methods: ['GET', 'POST'])]
    public function chooseTemplate($id, Request $request, EntityManagerInterface $entityManager): Response
    {

        $prices = $entityManager->getRepository(Price::class)->findAll();

        $priceChoices = [];
        foreach ($prices as $price) {
            $priceChoices[$price->getDescription()] = $price->getId();
        }

        $form = $this->createForm(OrderItemTemplateForm::class, null, [
            'price_choices' => $priceChoices
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $selectedPriceId = $formData['select_template'];

            $entityManager->flush();
            return $this->redirectToRoute('app_new_item', [
                'id' => $id,
                'price_template' => $selectedPriceId,
            ]);
        }

        return $this->render('order_item/choose_template.html.twig', [
            'form' => $form,
            'id' => $id,
        ]);
    }
}
