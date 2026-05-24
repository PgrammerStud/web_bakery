<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class CartController extends AbstractController
{
    #[Route('/add-to-cart/{id}', name: 'app_add_to_cart', methods: ['POST'])]
    public function addToCart(
        int $id,
        Request $request,
        CartService $cartService,
        ProductRepository $productRepository
    ): Response {
        if (!$this->getUser()) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $product = $productRepository->find($id);
        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], 404);
        }

        $quantity = max(1, $request->request->getInt('quantity', 1));
        $cartService->addToCart($id, $quantity);

        return new JsonResponse([
            'success' => true,
            'message' => "{$product->getName()} added to cart",
        ]);
    }

    #[Route('/cart', name: 'app_cart', methods: ['GET'])]
public function viewCart(Request $request, CartService $cartService): Response
{
    $authHeader = $request->headers->get('Authorization', '');
    $isApiRequest = str_starts_with($authHeader, 'Bearer ') 
                    && strlen(trim(substr($authHeader, 7))) > 0;

    if ($isApiRequest) {
        if (!$this->getUser()) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }
        return new JsonResponse($cartService->getCartWithDetails());
    }

    return $this->render('cart/view.html.twig', [
        'cartDetails' => $cartService->getCartWithDetails(),
        'cartTotal'   => $cartService->getCartTotal(),
        'itemCount'   => $cartService->getItemCount(),
    ]);
}

    #[Route('/remove-from-cart/{id}', name: 'app_remove_from_cart', methods: ['POST'])]
    public function removeFromCart(int $id, CartService $cartService): Response
    {
        $cartService->removeCartItemById($id);

        return new JsonResponse(['success' => true, 'message' => 'Item removed']);
    }

    #[Route('/cart/update/{id}', name: 'app_update_cart_item', methods: ['POST'])]
    public function updateCartItem(int $id, Request $request, CartService $cartService): Response
    {
        $quantity = max(1, $request->request->getInt('quantity', 1));
        $cartService->updateCartItemById($id, $quantity);

        return new JsonResponse(['success' => true, 'message' => 'Cart updated']);
    }

    #[Route('/cart/clear', name: 'app_clear_cart', methods: ['POST'])]
    public function clearCart(CartService $cartService): Response
    {
        $cartService->clearCart();

        return new JsonResponse(['success' => true, 'message' => 'Cart cleared']);
    }
}