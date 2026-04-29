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

    #[Route('/new', name: 'app_payment_new', methods: ['GET'])]
    public function newReact(): Response
    {
        return $this->render('payment/new.html.twig');
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
}
