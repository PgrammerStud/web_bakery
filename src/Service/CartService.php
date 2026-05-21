<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class CartService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ProductRepository $productRepository,
        private CartRepository $cartRepository,
        private Security $security,
    ) {}

    private function getOrCreateCart(): Cart
    {
        $user = $this->security->getUser();
        $cart = $this->cartRepository->findOneBy(['customer' => $user]);

        if (!$cart) {
            $cart = new Cart();
            $cart->setCustomer($user);
            $cart->setCreatedAt(new \DateTime());
            $cart->setUpdatedAt(new \DateTime());
            $this->em->persist($cart);
            $this->em->flush();
        }

        return $cart;
    }

    public function addToCart(int $productId, int $quantity = 1): bool
    {
        $product = $this->productRepository->find($productId);
        if (!$product) return false;

        $cart = $this->getOrCreateCart();

        foreach ($cart->getCartItems() as $item) {
            if ($item->getProduct()->getId() === $productId) {
                $item->setQuantity($item->getQuantity() + $quantity);
                $cart->setUpdatedAt(new \DateTime());
                $this->em->flush();
                return true;
            }
        }

        $cartItem = new CartItem();
        $cartItem->setCart($cart);
        $cartItem->setProduct($product);
        $cartItem->setQuantity($quantity);
        $cartItem->setAddedAt(new \DateTime());
        $this->em->persist($cartItem);

        $cart->setUpdatedAt(new \DateTime());
        $this->em->flush();
        return true;
    }

    public function removeCartItemById(int $cartItemId): bool
    {
        $cartItem = $this->em->getRepository(CartItem::class)->find($cartItemId);
        if (!$cartItem) return false;

        $this->em->remove($cartItem);
        $this->em->flush();
        return true;
    }

    public function updateCartItemById(int $cartItemId, int $quantity): bool
    {
        $cartItem = $this->em->getRepository(CartItem::class)->find($cartItemId);
        if (!$cartItem) return false;

        if ($quantity <= 0) {
            $this->em->remove($cartItem);
        } else {
            $cartItem->setQuantity($quantity);
        }

        $this->em->flush();
        return true;
    }

    public function getCartWithDetails(): array
    {
        $user = $this->security->getUser();
        $cart = $this->cartRepository->findOneBy(['customer' => $user]);
        if (!$cart) return [];

        $result = [];
        foreach ($cart->getCartItems() as $item) {
            $product = $item->getProduct();
            $result[] = [
                'id'          => $item->getId(),
                'productId'   => $product->getId(),
                'name'        => $product->getName(),
                'description' => $product->getDescription(),
                'price'       => $product->getPrice(),
                'imageUrl'    => $product->getImageUrl(),
                'quantity'    => $item->getQuantity(),
            ];
        }

        return $result;
    }

    public function getCartTotal(): float
    {
        return round(array_sum(array_map(
            fn($i) => (float)$i['price'] * $i['quantity'],
            $this->getCartWithDetails()
        )), 2);
    }

    public function getItemCount(): int
    {
        return array_sum(array_column($this->getCartWithDetails(), 'quantity'));
    }

    public function clearCart(): void
    {
        $user = $this->security->getUser();
        $cart = $this->cartRepository->findOneBy(['customer' => $user]);
        if (!$cart) return;

        foreach ($cart->getCartItems() as $item) {
            $this->em->remove($item);
        }
        $this->em->flush();
    }
}