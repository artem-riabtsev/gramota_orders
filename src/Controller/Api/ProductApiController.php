<?php

namespace App\Controller\Api;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/product')]
class ProductApiController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $em
    ) {}

    #[Route('/list', name: 'api_product_list', methods: ['GET'])]
    public function list(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $query = $request->query->get('q', '');
        $page = (int)$request->query->get('page', 1);
        $limit = (int)$request->query->get('limit', 10);
        $offset = ($page - 1) * $limit;

        if (empty($query)) {
            $products = $productRepository->findBy([], ['date' => 'DESC'], $limit, $offset);
            $total = $productRepository->count([]);
        } else {
            $products = $productRepository->findProductWithPagination($query, $limit, $offset);
            $total = $productRepository->countProductBySearch($query);
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

        return $this->json([
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ]);
    }

    #[Route('/search', name: 'api_product_search', methods: ['GET'])]
    public function search(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $query = $request->query->get('q', '');
        $basic = $request->query->get('basic'); // '1' или '0'

        $qb = $productRepository->createQueryBuilder('p')
            ->orderBy('p.description', 'ASC')
            ->setMaxResults(50);

        if ($basic === '1') {
            $qb->andWhere('p.basic = 1');
        }

        if (strlen($query) >= 2) {
            $qb->andWhere('p.description LIKE :q')
                ->setParameter('q', '%' . $query . '%');
        }

        $products = $qb->getQuery()->getResult();

        $data = array_map(function ($product) {
            return [
                'id' => $product->getId(),
                'description' => $product->getDescription(),
                'project' => $product->getProject()->getName(),
                'basic' => $product->getBasic()
            ];
        }, $products);

        return $this->json($data);
    }

    #[Route('/{id}/delete', name: 'api_product_delete', methods: ['DELETE'])]
    public function delete(Product $product): JsonResponse
    {
        if ($product->hasOrderItems()) {
            return $this->json(['success' => false, 'error' => 'Продукт используется в заказах'], 400);
        }
        if ($product->hasPrices()) {
            return $this->json(['success' => false, 'error' => 'Продукт есть в прайсе'], 400);
        }
        $this->em->remove($product);
        $this->em->flush();
        return $this->json(['success' => true]);
    }

    #[Route('/by-project', name: 'api_product_by_project', methods: ['GET'])]
    public function getByProject(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $projectId = $request->query->get('projectId');
        $query = $request->query->get('q', '');

        if (!$projectId) {
            return $this->json([]);
        }

        $qb = $productRepository->createQueryBuilder('p')
            ->where('p.project = :projectId')
            ->setParameter('projectId', $projectId)
            ->orderBy('p.description', 'ASC');

        if (!empty($query)) {
            $qb->andWhere('p.description LIKE :q')
                ->setParameter('q', '%' . $query . '%');
        }

        $products = $qb->setMaxResults(50)->getQuery()->getResult();

        $data = array_map(function ($product) {
            return [
                'id' => $product->getId(),
                'description' => $product->getDescription(),
                'basic' => $product->getBasic(),
                'date' => $product->getDate()->format('d.m.Y')
            ];
        }, $products);

        return $this->json($data);
    }
}
