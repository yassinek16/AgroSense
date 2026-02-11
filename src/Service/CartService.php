<?php

namespace App\Service;

use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    private SessionInterface $session;
    private EntityManagerInterface $entityManager;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager)
    {
        $this->session = $requestStack->getSession();
        $this->entityManager = $entityManager;
    }

    /**
     * Add a product to the cart
     */
    public function add(int $id): void
    {
        $cart = $this->session->get('cart', []);
        
        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $this->session->set('cart', $cart);
    }

    /**
     * Remove a product from the cart
     */
    public function remove(int $id): void
    {
        $cart = $this->session->get('cart', []);
        
        // Forcer la suppression si l'ID existe
        if (array_key_exists($id, $cart)) {
            unset($cart[$id]);
        }
        
        // Forcer la mise à jour de la session
        $this->session->set('cart', $cart);
        $this->session->save(); // Forcer la sauvegarde immédiate
    }

    /**
     * Update quantity of a product in the cart
     */
    public function updateQuantity(int $id, int $quantity): void
    {
        $cart = $this->session->get('cart', []);
        
        if ($quantity <= 0) {
            unset($cart[$id]);
        } else {
            $cart[$id] = $quantity;
        }

        $this->session->set('cart', $cart);
    }

    /**
     * Get the full cart with products
     */
    public function getFullCart(): array
    {
        $cart = $this->session->get('cart', []);
        $cartWithData = [];

        foreach ($cart as $id => $quantity) {
            $product = $this->entityManager->getRepository(Produit::class)->find($id);
            if ($product) {
                $cartWithData[$id] = [
                    'product' => $product,
                    'quantity' => $quantity
                ];
            }
        }

        return $cartWithData;
    }

    /**
     * Get the total amount of the cart
     */
    public function getTotal(): float
    {
        $total = 0;
        $cartWithData = $this->getFullCart();

        foreach ($cartWithData as $item) {
            $total += $item['product']->getPrix() * $item['quantity'];
        }

        return $total;
    }

    /**
     * Get the number of items in the cart
     */
    public function getItemCount(): int
    {
        $cart = $this->session->get('cart', []);
        return array_sum($cart);
    }

    /**
     * Clear the cart
     */
    public function clear(): void
    {
        $this->session->remove('cart');
    }

    /**
     * Get the raw cart data (product IDs and quantities)
     */
    public function getCart(): array
    {
        return $this->session->get('cart', []);
    }

    /**
     * Check if a product is in the cart
     */
    public function hasProduct(int $id): bool
    {
        $cart = $this->session->get('cart', []);
        return isset($cart[$id]);
    }

    /**
     * Get the quantity of a specific product in the cart
     */
    public function getProductQuantity(int $id): int
    {
        $cart = $this->session->get('cart', []);
        return $cart[$id] ?? 0;
    }
}
