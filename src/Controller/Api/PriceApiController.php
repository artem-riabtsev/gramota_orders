<?php

namespace App\Controller\Api;

use App\Entity\Price;
use App\Repository\PriceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/price')]
class PriceApiController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $em
    ) {}

    #[Route('/list', name: 'api_price_list', methods: ['GET'])]
    public function list(Request $request, PriceRepository $priceRepository): JsonResponse
    {
        $query = $request->query->get('q', '');
        $page = (int)$request->query->get('page', 1);
        $limit = (int)$request->query->get('limit', 10);
        $offset = ($page - 1) * $limit;

        if (empty($query)) {
            $prices = $priceRepository->findBy([], ['description' => 'ASC'], $limit, $offset);
            $total = $priceRepository->count([]);
        } else {
            $prices = $priceRepository->findPriceWithPagination($query, $limit, $offset);
            $total = $priceRepository->countPriceBySearch($query);
        }

        $data = array_map(function ($price) {
            return [
                'id' => $price->getId(),
                'description' => $price->getDescription(),
                'price' => $price->getPrice()->getAmount(),
                'product' => [
                    'id' => $price->getProduct()->getId(),
                    'description' => $price->getProduct()->getDescription()
                ]
            ];
        }, $prices);

        return $this->json([
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ]);
    }

    #[Route('/{id}/delete', name: 'api_price_delete', methods: ['DELETE'])]
    public function delete(Price $price): JsonResponse
    {
        $this->em->remove($price);
        $this->em->flush();
        return $this->json(['success' => true]);
    }
}
