<?php

namespace App\Controller\Api;

use App\Repository\CustomerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/customer')]
class CustomerApiController extends AbstractController
{
    #[Route('/search', name: 'api_customer_search', methods: ['GET'])]
    public function search(Request $request, CustomerRepository $customerRepository): JsonResponse
    {
        $query = $request->query->get('q', '');

        if (strlen($query) < 2) {
            return $this->json([]);
        }

        $customers = $customerRepository->findCustomers($query);

        $data = array_map(function ($customer) {
            return [
                'id' => $customer->getId(),
                'name' => $customer->getName(),
                'email' => $customer->getEmail(),
                'phone' => $customer->getPhone()
            ];
        }, $customers);

        return $this->json($data);
    }
}
