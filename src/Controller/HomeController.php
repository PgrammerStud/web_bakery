<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\BakeitforwardwalletRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        ProductRepository $productRepository,
        BakeitforwardwalletRepository $walletRepository
    ): Response {
        // Fetch featured products (limit to 3)
        $featuredProducts = $productRepository->findBy([], ['created_at' => 'DESC'], 3);

        // Fetch donation wallet
        $wallet = $walletRepository->findOneBy([]);

        return $this->render('home/index.html.twig', [
            'featuredProducts' => $featuredProducts,
            'wallet' => $wallet,
        ]);
    }

    #[Route('/products', name: 'app_products', methods: ['GET'])]
    public function products(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        return $this->render('product/products.html.twig', [
            'products' => $products,
        ]);
    }
}

