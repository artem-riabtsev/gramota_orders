<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/report')]
class ReportController extends AbstractController
{
    #[Route('/order-items', name: 'app_report_order_items')]
    public function orderItems(): Response
    {
        return $this->render('report/order_items.html.twig');
    }
}
