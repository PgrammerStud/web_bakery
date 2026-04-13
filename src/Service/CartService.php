<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private const CART_SESSION_KEY = 'cart_items';
    
    private RequestStack $requestStack;
    private ProductRepository $productRepository;

    public function __construct(RequestStack $requestStack, ProductRepository $productRepository)
    {
        $this->requestStack = $requestStack;
        $this->productRepository = $productRepository;
    }

    /**
     * Add or update a product in the cart
     */
    public function addToCart(int $productId, int $quantity = 1): bool
    {
        // Verify product exists
        $product = $this->productRepository->find($productId);
        if (!$product) {
            return false;
        }

        $session = $this->requestStack->getSession();
        $cart = $session->get(self::CART_SESSION_KEY, []);

        // If product already in cart, increase quantity
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            // Add new product to cart
            $cart[$productId] = [
                'quantity' => $quantity,
                'price' => $product->getPrice(),
                'name' => $product->getName(),
            ];
        }

        $session->set(self::CART_SESSION_KEY, $cart);
        return true;
    }

    /**
     * Remove a product from the cart
     */
    public function removeFromCart(int $productId): bool
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::CART_SESSION_KEY, []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            $session->set(self::CART_SESSION_KEY, $cart);
            return true;
        }

        return false;
    }

    /**
     * Update quantity of a product in the cart
     */
    public function updateQuantity(int $productId, int $quantity): bool
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::CART_SESSION_KEY, []);

        if (!isset($cart[$productId])) {
            return false;
        }

        if ($quantity <= 0) {
            return $this->removeFromCart($productId);
        }

        $cart[$productId]['quantity'] = $quantity;
        $session->set(self::CART_SESSION_KEY, $cart);
        return true;
    }

    /**
     * Get all cart items
     */
    public function getCart(): array
    {
        $session = $this->requestStack->getSession();
        return $session->get(self::CART_SESSION_KEY, []);
    }

    /**
     * Get cart item count
     */
    public function getItemCount(): int
    {
        $cart = $this->getCart();
        $count = 0;

        foreach ($cart as $item) {
            $count += $item['quantity'];
        }

        return $count;
    }

    /**
     * Get cart total price
     */
    public function getCartTotal(): float
    {
        $cart = $this->getCart();
        $total = 0;

        foreach ($cart as $item) {
            $total += (float)$item['price'] * $item['quantity'];
        }

        return round($total, 2);
    }

    /**
     * Clear entire cart
     */
    public function clearCart(): void
    {
        $session = $this->requestStack->getSession();
        $session->remove(self::CART_SESSION_KEY);
    }

    /**
     * Check if product is in cart
     */
    public function isInCart(int $productId): bool
    {
        $cart = $this->getCart();
        return isset($cart[$productId]);
    }

    /**
     * Get cart with product details (full product objects)
     */
    public function getCartWithDetails(): array
    {
        $cart = $this->getCart();
        $cartDetails = [];

        foreach ($cart as $productId => $item) {
            $product = $this->productRepository->find($productId);
            if ($product) {
                $cartDetails[$productId] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => (float)$item['price'] * $item['quantity'],
                ];
            }
        }

        return $cartDetails;
    }
}
