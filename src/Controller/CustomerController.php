<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Form\CustomerForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Config\OrderStatus;

#[Route('/customer')]
final class CustomerController extends AbstractController
{

    #[Route(name: 'app_customer_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('customer/index.html.twig');
    }

    #[Route('/new', name: 'app_customer_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $from = $request->query->get('from');
        if ($from) {
            $request->getSession()->set('from', $from);
        }

        $customer = new Customer();
        $form = $this->createForm(CustomerForm::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($customer);
            $em->flush();

            $from = $request->getSession()->get('from');
            $request->getSession()->remove('from');
            if ($from === 'order') {
                return $this->redirectToRoute('app_order_new', [
                    'customer' => $customer->getId()
                ]);
            }

            return $this->redirectToRoute('app_customer_index');
        }

        return $this->render('customer/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_customer_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Customer $customer): Response
    {
        $ordersGroups = [];

        foreach (OrderStatus::cases() as $status) {
            $ordersGroups[] = [
                'name' => $status->label(),
                'orders' => $customer->getOrders()->filter(fn($order) => $order->getStatus() === $status),
                'color' => $status->color(),
            ];
        }

        return $this->render('customer/show.html.twig', [
            'customer' => $customer,
            'ordersGroups' => $ordersGroups,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_customer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Customer $customer, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CustomerForm::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_customer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('customer/edit.html.twig', [
            'customer' => $customer,
            'form' => $form,
        ]);
    }
}
