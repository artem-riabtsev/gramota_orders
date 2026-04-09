<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Form\PaymentForm;
use App\Form\PaymentNewForm;
use App\Repository\PaymentRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use \DateTime;
use DateTimeImmutable;

#[Route('/payment')]
final class PaymentController extends AbstractController
{

    #[Route(name: 'app_payment_index')]
    public function indexReact(): Response
    {
        return $this->render('payment/index.html.twig');
    }

    #[Route('/new', name: 'app_payment_new', methods: ['GET', 'POST'])]
    public function select(Request $request, EntityManagerInterface $entityManager, OrderRepository $orderRepository): Response
    {
        $payment = new Payment();
        $form = $this->createForm(PaymentNewForm::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($payment);
            $entityManager->flush();

            return $this->redirectToRoute('app_payment_edit', ['id' => $payment->getId()]);
        }

        $searchQuery = $request->query->get('q') ?? '';
        return $this->render('payment/new.html.twig', [
            'orders' => $orderRepository->findOrders($searchQuery),
            'searchQuery' => $searchQuery,
            'form' => $form
        ]);
    }

    #[Route('/{id}/edit', name: 'app_payment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Payment $payment, EntityManagerInterface $entityManager, PaymentRepository $paymentRepository): Response
    {
        $form = $this->createForm(PaymentForm::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order = $payment->getOrder();
            $entityManager->persist($order);
            $entityManager->flush();

            return $this->redirectToRoute('app_payment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('payment/edit.html.twig', [
            'payment' => $payment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_payment_delete', methods: ['POST'])]
    public function delete(Request $request, Payment $payment, EntityManagerInterface $entityManager, PaymentRepository $paymentRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $payment->getId(), $request->getPayload()->getString('_token'))) {
            $order = $payment->getOrder();

            $order->removePayment($payment);

            $entityManager->persist($order);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_payment_index', [], Response::HTTP_SEE_OTHER);
    }
}
