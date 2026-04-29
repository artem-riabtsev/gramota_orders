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
    public function index(): Response
    {
        return $this->render('order/index.html.twig');
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
}
