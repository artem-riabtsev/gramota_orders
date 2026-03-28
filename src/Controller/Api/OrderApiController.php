<?php

namespace App\Controller\Api;

use App\Entity\Order;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/order')]
class OrderApiController extends AbstractController
{
    #[Route('/create', name: 'api_order_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, CustomerRepository $customerRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['customerId'])) {
            return $this->json(['success' => false, 'error' => 'Не выбран заказчик'], 400);
        }

        $customer = $customerRepository->find($data['customerId']);

        if (!$customer) {
            return $this->json(['success' => false, 'error' => 'Заказчик не найден'], 404);
        }

        $order = new Order();
        $order->setCustomer($customer);

        $em->persist($order);
        $em->flush();

        return $this->json([
            'success' => true,
            'orderId' => $order->getId()
        ]);
    }
}
