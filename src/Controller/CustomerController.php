<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Form\CustomerForm;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/customer')]
final class CustomerController extends AbstractController
{

    #[Route(name: 'app_customer_index', methods: ['GET'])]
    public function index(
        Request $request,
        CustomerRepository $customerRepository
    ): Response {
        $filters = [
            'id' => $request->query->get('id'),
            'surname' => $request->query->get('surname'),
            'name' => $request->query->get('name'),
            'patronymic' => $request->query->get('patronymic'),
            'email' => $request->query->get('email'),
            'phone' => $request->query->get('phone'),
        ];

        $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');

        $customers = $customerRepository->findByFilters($filters);

        $allCustomers = $customerRepository->findAll();

        return $this->render('customer/index.html.twig', [
            'customers' => $customers,
            'filters' => $filters,
            'allCustomers' => $allCustomers,
        ]);
    }

    #[Route('/customer/new', name: 'app_customer_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $from = $request->query->get('from');
        if ($from) {
            $request->getSession()->set('from', $from);
        }

        $customer = new Customer();
        $form = $this->createForm(CustomerForm::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($customer);
            $em->flush();

            $from = $request->getSession()->get('from');
            if ($from === 'order') {
                return $this->redirectToRoute('app_order_new', [
                    'customer' => $customer->getId()
                ]);
            }

            return $this->redirectToRoute('app_customer_index');
        }

        return $this->render('customer/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/select', name: 'app_customer_select')]
    public function select(Request $request, CustomerRepository $customerRepository): Response
    {
        $query = $request->query->get('q');

        if ($query) {
            $customers = $customerRepository->createQueryBuilder('c')
                ->where('LOWER(c.surname) LIKE :q')
                ->orWhere('LOWER(c.name) LIKE :q')
                ->orWhere('LOWER(c.patronymic) LIKE :q')
                ->orWhere('LOWER(c.email) LIKE :q')
                ->setParameter('q', '%' . strtolower($query) . '%')
                ->orderBy('c.surname', 'ASC')
                ->getQuery()
                ->getResult();
        } else {
            $customers = $customerRepository->findBy([], ['surname' => 'ASC']);
        }

        return $this->render('customer/select.html.twig', [
            'customers' => $customers,
        ]);
    }

    #[Route('/{id}', name: 'app_customer_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Customer $customer): Response
    {   $completedOrders = $customer->getOrders()->filter(fn($order) => $order->getStatus() === 1);
        $incompletedOrders = $customer->getOrders()->filter(fn($order) => $order->getStatus() === 0);
        return $this->render('customer/show.html.twig', [
            'customer' => $customer,
            'completedOrders' => $completedOrders,
            'incompletedOrders' => $incompletedOrders,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_customer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Customer $customer, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CustomerForm::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_customer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('customer/edit.html.twig', [
            'customer' => $customer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_customer_delete', methods: ['POST'])]
    public function delete(Request $request, Customer $customer, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$customer->getId(), $request->getPayload()->getString('_token'))) {

            if ($customer->getOrders()->count() > 0) {
            $this->addFlash('danger', 'Нельзя удалить заказчика, у которого есть заказы.');
            return $this->redirectToRoute('app_customer_index');
        }
        
            $entityManager->remove($customer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_customer_index', [], Response::HTTP_SEE_OTHER);
    }


}
