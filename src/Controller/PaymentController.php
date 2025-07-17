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


    #[Route('/phpinfo', name: 'phpinfo')]
    public function phpinfo(): Response
    {
        phpinfo();
        exit;
    }


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

        $entityManager->persist($payment);
        $entityManager->flush();

        return $this->redirectToRoute('app_payment_edit', ['id' => $payment->getId()], Response::HTTP_SEE_OTHER);

    }

    #[Route('/{id}/edit', name: 'app_payment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, $id, Payment $payment, OrderRepository $orderRepository, EntityManagerInterface $entityManager, PaymentRepository $paymentRepository): Response
    {
        $form = $this->createForm(PaymentForm::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $paymentRepository->updateOrderPaymentAmount($id, $payment->getAmount());

            $order = $payment->getOrder();

            $orderPaymentAmount = $order->getPaymentAmount();
            $orderAmount = $order->getAmount();
            if (bccomp($orderPaymentAmount, '0', 2) === 0 && bccomp($orderPaymentAmount, $orderAmount, 2) === -1) {
                $order->setStatus(1); // не оплачен
            } elseif (bccomp($orderPaymentAmount, '0', 2) === 1 && bccomp($orderPaymentAmount, $orderAmount, 2) === -1) {
                $order->setStatus(2); // частично оплачен
            } elseif (bccomp($orderPaymentAmount, $orderAmount, 2) === 0) {
                $order->setStatus(4); // оплачен
            } elseif (bccomp($orderPaymentAmount, $orderAmount, 2) === 1) {
                $order->setStatus(3); // переплата
            }

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
        if ($this->isCsrfTokenValid('delete'.$payment->getId(), $request->getPayload()->getString('_token'))) {
            $order = $payment->getOrder();
            $entityManager->remove($payment);
            $entityManager->flush();
            $paymentRepository->recalculateOrderPaymentAmount($payment->getOrder());
            $orderPaymentAmount = $order->getPaymentAmount();
            $orderAmount = $order->getAmount();
            if (bccomp($orderPaymentAmount, '0', 2) === 0 && bccomp($orderPaymentAmount, $orderAmount, 2) === -1) {
                $order->setStatus(1); // не оплачен
            } elseif (bccomp($orderPaymentAmount, '0', 2) === 1 && bccomp($orderPaymentAmount, $orderAmount, 2) === -1) {
                $order->setStatus(2); // частично оплачен
            } elseif (bccomp($orderPaymentAmount, $orderAmount, 2) === 0) {
                $order->setStatus(4); // оплачен
            } elseif (bccomp($orderPaymentAmount, $orderAmount, 2) === 1) {
                $order->setStatus(3); // переплата
            }

            $entityManager->persist($order);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_payment_index', [], Response::HTTP_SEE_OTHER);
    }
}
