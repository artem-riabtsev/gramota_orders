<?php

namespace App\Controller\Api;

use App\Repository\OrderItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/report')]
class ReportApiController extends AbstractController
{
    #[Route('/order-items', name: 'api_report_order_items', methods: ['GET'])]
    public function orderItems(Request $request, OrderItemRepository $orderItemRepository): JsonResponse
    {
        $query = $request->query->get('q', '');
        $page = (int)$request->query->get('page', 1);
        $limit = (int)$request->query->get('limit', 10);
        $offset = ($page - 1) * $limit;

        $dateFrom = $request->query->get('dateFrom');
        $dateTo = $request->query->get('dateTo');
        $status = $request->query->get('status');

        $qb = $orderItemRepository->createQueryBuilder('oi')
            ->leftJoin('oi.order', 'o')
            ->leftJoin('oi.product', 'p')
            ->addSelect('CASE
                WHEN o.status = 3 THEN 1
                WHEN o.status = 4 THEN 2
                WHEN o.status = 2 THEN 3
                WHEN o.status = 1 THEN 4
                ELSE 5
            END AS HIDDEN status_order')
            ->orderBy('status_order', 'ASC')
            ->addOrderBy('o.date', 'ASC')
            ->addOrderBy('o.date', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        // Поиск по продукту
        if (!empty($query)) {
            $qb->andWhere('p.description LIKE :q')
                ->setParameter('q', '%' . $query . '%');
        }

        // Фильтр по датам
        if ($dateFrom && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
            $qb->andWhere('o.date >= :dateFrom')
                ->setParameter('dateFrom', new \DateTimeImmutable($dateFrom));
        }
        if ($dateTo && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
            $qb->andWhere('o.date <= :dateTo')
                ->setParameter('dateTo', new \DateTimeImmutable($dateTo . ' 23:59:59'));
        }

        // Фильтр по статусу
        if ($status !== null && $status !== '' && ctype_digit((string)$status)) {
            $qb->andWhere('o.status = :status')
                ->setParameter('status', (int)$status);
        }

        $items = $qb->getQuery()->getResult();

        // Подсчет
        $countQb = $orderItemRepository->createQueryBuilder('oi')
            ->select('COUNT(oi.id)')
            ->leftJoin('oi.order', 'o');

        if (!empty($query)) {
            $countQb->andWhere('oi.description LIKE :q')
                ->setParameter('q', '%' . $query . '%');
        }
        if ($dateFrom && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
            $countQb->andWhere('o.date >= :dateFrom')
                ->setParameter('dateFrom', new \DateTimeImmutable($dateFrom));
        }
        if ($dateTo && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
            $countQb->andWhere('o.date <= :dateTo')
                ->setParameter('dateTo', new \DateTimeImmutable($dateTo . ' 23:59:59'));
        }
        if ($status !== null && $status !== '' && ctype_digit((string)$status)) {
            $countQb->andWhere('o.status = :status')
                ->setParameter('status', (int)$status);
        }

        $total = (int)$countQb->getQuery()->getSingleScalarResult();

        $data = array_map(function ($item) {
            return [
                'id' => $item->getId(),
                'description' => $item->getDescription(),
                'lineTotal' => (float) $item->getLineTotal()->getAmount()->toFloat(),
                'orderId' => $item->getOrder()->getId(),
                'orderDate' => $item->getOrder()->getDate()->format('d.m.Y'),
                'orderStatus' => [
                    'value' => $item->getOrder()->getStatus()->value,
                    'label' => $item->getOrder()->getStatus()->label(),
                    'color' => $item->getOrder()->getStatus()->color()
                ],
                'product' => $item->getProduct() ? [
                    'id' => $item->getProduct()->getId(),
                    'description' => $item->getProduct()->getDescription(),
                    'projectId' => $item->getProduct()->getProject()->getId(),
                    'basic' => $item->getProduct()->getBasic()
                ] : null,
            ];
        }, $items);

        return $this->json([
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ]);
    }
}
