<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Form\PaymentForm;
use App\Repository\PaymentRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Config\OrderStatus;
use \DateTime;
use DateTimeImmutable;

#[Route('/payment')]
final class PaymentController extends AbstractController
{

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

    #[Route(name: 'app_payment_index', methods: ['GET'])]
    public function index(Request $request, PaymentRepository $paymentRepository): Response
    {
        $query = $request->query->get('q');
        $from = $request->query->get('from');
        $to = $request->query->get('to');

        if ($from && $to) {
            $payments = $paymentRepository->findByDateRange(new \DateTime($from), new \DateTime($to));
        } elseif ($query) {
            $payments = $paymentRepository->findByOrderId($query);
        } else {
            $payments = $paymentRepository->findLastMonthOrders();
        }

        // $totalAmount = array_reduce($payments, fn($carry, $p) => $carry + $p->getAmount(), 0);

        return $this->render('payment/index.html.twig', [
            'payments' => $payments,
            'query' => $query,
            // 'totalAmount' => $totalAmount,
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

        $payment->setDate(new DateTimeImmutable());

        $entityManager->persist($payment);
        $entityManager->flush();

        return $this->redirectToRoute('app_payment_edit', ['id' => $payment->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/edit', name: 'app_payment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Payment $payment, EntityManagerInterface $entityManager, PaymentRepository $paymentRepository): Response
    {
        $form = $this->createForm(PaymentForm::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $entityManager->flush();
            // $paymentRepository->updateOrderPaymentAmount($id, $payment->getAmount());
            $paymentRepository->recalculateOrderPaymentAmount($payment->getOrder());

            $order = $payment->getOrder();

            $totalPaid = $order->getTotalPaid();
            $orderTotal = $order->getOrderTotal();
            // $order->recalcStatus($totalPaid, $orderTotal);
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
            $entityManager->remove($payment);
            $entityManager->flush();
            $paymentRepository->recalculateOrderPaymentAmount($payment->getOrder());
            $totalPaid = $order->getTotalPaid();
            $orderTotal = $order->getOrderTotal();
            // $order->recalcStatus($totalPaid, $orderTotal);

            $entityManager->persist($order);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_payment_index', [], Response::HTTP_SEE_OTHER);
    }
}
