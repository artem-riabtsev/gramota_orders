<?php

namespace App\Controller\Api;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/product')]
class ProductApiController extends AbstractController
{
    #[Route('/list', name: 'api_product_list', methods: ['GET'])]
    public function list(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $query = $request->query->get('q', '');

        if (empty($query)) {
            $products = $productRepository->findBy([], ['date' => 'DESC']);
        } else {
            $products = $productRepository->findProduct($query);
        }

        $data = array_map(function ($product) {
            return [
                'id' => $product->getId(),
                'description' => $product->getDescription(),
                'project' => $product->getProject()->getName(),
                'date' => $product->getDate()->format('d.m.Y'),
                'basic' => $product->getBasic()
            ];
        }, $products);

        return $this->json($data);
    }
}
