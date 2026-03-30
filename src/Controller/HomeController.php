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
        // Fetch featured products (limit to 6)
        $featuredProducts = $productRepository->findBy([], ['created_at' => 'DESC'], 6);

        // Fetch donation wallet
        $wallet = $walletRepository->findOneBy([]);

        return $this->render('home/index.html.twig', [
            'featuredProducts' => $featuredProducts,
            'wallet' => $wallet,
        ]);
    }
}
