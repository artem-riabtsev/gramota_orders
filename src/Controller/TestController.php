<?php

namespace App\Controller;

use App\Entity\Price;
use App\Entity\Product;
use App\Form\PriceForm;
use App\Repository\PriceRepository;
use Brick\Money\Money;
use Doctrine\ORM\EntityManagerInterface as EM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TestController extends AbstractController
{

    #[Route('/test', methods: ['GET'])]
    public function index(Request $request, EM $em): Response
    {
        $money = Money::of(99.99, 'RUB');
        // $money->isGreaterThan();

        $price = new Price;
        $price->setPrice('0.00');
        $price->setDescription('Ура');
        $price->setProduct($em->getRepository(Product::class)->find(11));

        $price->setPrice1($money);


        $em->persist($price);
        $em->flush();

        // return new Response($price->getPrice1()->getAmount());
        // dd((string)$price->getPrice1()->getAmount());
        // dd($price->getProduct());


        return $this->render('price/index1.html.twig', ['price' => $price]);
    }

    #[Route('/test/{id}', methods: ['GET', 'POST'])]
    public function edit(Request $request, EM $em, Price $price): Response
    {

        $form = $this->createForm(PriceForm::class, $price);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            // return $this->redirectToRoute('app_price_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('price/edit.html.twig', [
            'price' => $price,
            'form' => $form,
        ]);
    }
}
