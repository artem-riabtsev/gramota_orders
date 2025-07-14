<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Payment;
use App\Form\PaymentForm;
use App\Repository\PaymentRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use \DateTime;

#[Route('/payment')]
final class PaymentController extends AbstractController
{
    #[Route(name: 'app_payment_index', methods: ['GET'])]
    public function index(Request $request ,PaymentRepository $paymentRepository): Response
    {
        $query = $request->query->get('q');

        if ($query) {
            $payments = $paymentRepository->findByOrderId($query);
        } else {
            $payments = $paymentRepository->findLastMonthOrders();
        }

        return $this->render('payment/index.html.twig', [
            'payments' => $payments,
            'query' => $query,
        ]);
    }

    #[Route('/new', name: 'app_payment_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request, 
        EntityManagerInterface $entityManager, 
        PaymentRepository $paymentRepository,
        OrderRepository $orderRepository
    ): Response {
        $payment = new Payment();

        $orderId = $request->query->get('order');
        if ($orderId) {
            $order = $orderRepository->find($orderId);
            if ($order) {
                $payment->setOrder($order);
            }
        }

        $payment->setDate(new DateTime());
        $form = $this->createForm(PaymentForm::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($payment);
            $entityManager->flush();
            $paymentRepository->recalculateOrderPaymentAmount($payment->getOrder());


            return $this->redirectToRoute('app_payment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('payment/new.html.twig', [
            'payment' => $payment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_payment_show', methods: ['GET'])]
    public function show(Payment $payment): Response
    {
        return $this->render('payment/show.html.twig', [
            'payment' => $payment,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_payment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, $id, Payment $payment, EntityManagerInterface $entityManager, PaymentRepository $paymentRepository): Response
    {
        $form = $this->createForm(PaymentForm::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $paymentRepository->updateOrderPaymentAmount($id, $payment->getAmount());

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
        if ($this->isCsrfTokenValid('delete'.$payment->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($payment);
            $entityManager->flush();
            $paymentRepository->recalculateOrderPaymentAmount($payment->getOrder());
        }

        return $this->redirectToRoute('app_payment_index', [], Response::HTTP_SEE_OTHER);
    }
}
