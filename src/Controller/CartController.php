<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('')]
final class CartController extends AbstractController
{
    /**
     * Display all products for purchase
     */
    #[Route('/products', name: 'app_products', methods: ['GET'])]
    public function products(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        return $this->render('cart/products.html.twig', [
            'products' => $products,
        ]);
    }

    /**
     * Add a product to cart - requires authentication
     */
    #[Route('/add-to-cart/{id}', name: 'app_add_to_cart', methods: ['POST'])]
    public function addToCart(
        int $id,
        Request $request,
        CartService $cartService,
        ProductRepository $productRepository
    ): Response {
        // Check if user is logged in
        if (!$this->getUser()) {
            $this->addFlash('warning', 'Please log in to add items to your cart.');
            return $this->redirectToRoute('app_login', [
                'redirect_to' => $this->generateUrl('app_products'),
            ]);
        }

        // Verify product exists
        $product = $productRepository->find($id);
        if (!$product) {
            $this->addFlash('error', 'Product not found.');
            return $this->redirectToRoute('app_products');
        }

        // Get quantity from request (default to 1)
        $quantity = $request->request->getInt('quantity', 1);
        if ($quantity < 1) {
            $quantity = 1;
        }

        // Add to cart
        $cartService->addToCart($id, $quantity);
        
        $this->addFlash('success', "{$product->getName()} has been added to your cart!");

        // Redirect back to products or cart
        $redirectTo = $request->request->get('redirect_to', 'app_products');
        return $this->redirectToRoute($redirectTo);
    }

    /**
     * View cart page
     */
    #[Route('/cart', name: 'app_cart', methods: ['GET'])]
    public function viewCart(CartService $cartService): Response
    {
        $cartDetails = $cartService->getCartWithDetails();
        $cartTotal = $cartService->getCartTotal();
        $itemCount = $cartService->getItemCount();

        return $this->render('cart/view.html.twig', [
            'cartDetails' => $cartDetails,
            'cartTotal' => $cartTotal,
            'itemCount' => $itemCount,
        ]);
    }

    /**
     * Remove product from cart
     */
    #[Route('/remove-from-cart/{id}', name: 'app_remove_from_cart', methods: ['POST'])]
    public function removeFromCart(
        int $id,
        CartService $cartService,
        ProductRepository $productRepository
    ): Response {
        $product = $productRepository->find($id);
        
        $cartService->removeFromCart($id);
        
        if ($product) {
            $this->addFlash('success', "{$product->getName()} has been removed from your cart.");
        }

        return $this->redirectToRoute('app_cart');
    }

    /**
     * Update cart item quantity (via POST for security)
     */
    #[Route('/cart/update/{id}', name: 'app_update_cart_item', methods: ['POST'])]
    public function updateCartItem(
        int $id,
        Request $request,
        CartService $cartService
    ): Response {
        $quantity = $request->request->getInt('quantity', 1);
        
        if ($quantity < 1) {
            $cartService->removeFromCart($id);
            $this->addFlash('success', 'Item removed from cart.');
        } else {
            $cartService->updateQuantity($id, $quantity);
            $this->addFlash('success', 'Cart updated.');
        }

        return $this->redirectToRoute('app_cart');
    }

    /**
     * Clear entire cart
     */
    #[Route('/cart/clear', name: 'app_clear_cart', methods: ['POST'])]
    public function clearCart(CartService $cartService): Response
    {
        $cartService->clearCart();
        $this->addFlash('success', 'Your cart has been cleared.');

        return $this->redirectToRoute('app_cart');
    }
}
