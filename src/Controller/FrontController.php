<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FrontController extends AbstractController
{
    #[Route('/', name: 'app_front_index', methods: ['GET', 'POST'])]
    public function index(ProduitRepository $produitRepository, Request $request): Response
    {
        $searchTerm = $request->query->get('search', '');
        
        if ($searchTerm) {
            $produits = $produitRepository->findBySearchTerm($searchTerm);
        } else {
            $produits = $produitRepository->findAll();
        }
        
        return $this->render('front/index.html.twig', [
            'produits' => $produits,
            'searchTerm' => $searchTerm,
        ]);
    }

    #[Route('/front', name: 'app_front_redirect', methods: ['GET'])]
    public function redirectFront(): Response
    {
        return $this->redirectToRoute('app_front_index');
    }

    #[Route('/cart', name: 'app_cart', methods: ['GET', 'POST'])]
    public function cart(Request $request, \App\Service\CartService $cartService, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $cart = $request->request->all('cart');
            
            foreach ($cart as $productId => $quantity) {
                $cartService->updateQuantity((int)$productId, (int)$quantity);
            }
            
            // Rediriger vers la page du panier avec les nouvelles données
            return $this->redirectToRoute('app_cart');
        }

        $cartWithData = $cartService->getFullCart();
        $total = $cartService->getTotal();

        return $this->render('front/cart.html.twig', [
            'cart' => $cartWithData,
            'total' => $total,
        ]);
    }

   #[Route('/cart/add/{id}', name: 'app_cart_add', methods: ['POST'])]
public function addToCart(
    int $id, 
    \App\Service\CartService $cartService, 
    \App\Repository\ProduitRepository $produitRepository
): Response {
    // 1. Récupérer le produit depuis la base de données
    $produit = $produitRepository->find($id);

    // 2. Vérifier si le produit existe
    if (!$produit) {
        $this->addFlash('danger', 'Produit inexistant.');
        return $this->redirectToRoute('app_front_index');
    }

    // 3. Récupérer la quantité déjà présente dans le panier (si elle existe)
    $panier = $cartService->getCart();
    $quantiteDansPanier = $panier[$id] ?? 0;

    // 4. Vérifier si on peut ajouter une unité supplémentaire
    if ($quantiteDansPanier + 1 > $produit->getQuantiteStock()) {
        $this->addFlash('warning', sprintf(
            'Action impossible : Il ne reste que %d unité(s) en stock pour le produit "%s".',
            $produit->getQuantiteStock(),
            $produit->getNom()
        ));
    } else {
        // 5. Ajouter au panier seulement si le stock le permet
        $cartService->add($id);
        $this->addFlash('success', 'Produit ajouté au panier avec succès !');
    }

    return $this->redirectToRoute('app_front_index');
}
    #[Route('/cart/update/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function updateCart(int $id, Request $request, \App\Service\CartService $cartService): Response
    {
        $quantity = $request->request->get('quantity', 1);
        
        if ($quantity > 0) {
            $cartService->updateQuantity($id, (int)$quantity);
        }
        
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function removeFromCart(int $id, \App\Service\CartService $cartService): Response
    {
        // Forcer la suppression
        $cartService->remove($id);
        
        // Forcer le rafraîchissement des données du panier
        $cartWithData = $cartService->getFullCart();
        $total = $cartService->getTotal();
        
        if (empty($cartWithData)) {
            $this->addFlash('info', 'Votre panier est maintenant vide.');
        } else {
            $this->addFlash('success', 'Produit supprimé du panier avec succès!');
        }
        
        // Rendre directement le template avec les nouvelles données
        return $this->render('front/cart.html.twig', [
            'cart' => $cartWithData,
            'total' => $total,
        ]);
    }

    #[Route('/checkout', name: 'app_checkout', methods: ['POST'])]
    public function checkout(\App\Service\CartService $cartService, EntityManagerInterface $entityManager): Response
    {
        $cartWithData = $cartService->getFullCart();
        
        if (empty($cartWithData)) {
            $this->addFlash('error', 'Votre panier est vide!');
            return $this->redirectToRoute('app_cart');
        }

        $commande = new \App\Entity\Commande();
        $commande->setReference('CMD-' . uniqid());
        $commande->setDateCommande(new \DateTime());
        $commande->setTotal($cartService->getTotal());
        $commande->setCreatedAt(new \DateTimeImmutable());
        $commande->setStatut('Confirmée');

        foreach ($cartWithData as $item) {
            $produit = $item['product'];
            $quantity = $item['quantity'];

            // Check if there's enough stock
            if ($produit->getQuantiteStock() < $quantity) {
                $this->addFlash('error', 'Stock insuffisant pour le produit: ' . $produit->getNom());
                return $this->redirectToRoute('app_cart');
            }

            // Create LigneCommande
            $ligneCommande = new \App\Entity\LigneCommande();
            $ligneCommande->setProduit($produit);
            $ligneCommande->setCommande($commande);
            $ligneCommande->setQuantite($quantity);
            $ligneCommande->setPrixUnitaire($produit->getPrix());
            $entityManager->persist($ligneCommande);

            // Update product stock in database
            $produit->setQuantiteStock($produit->getQuantiteStock() - $quantity);
            $entityManager->persist($produit);
        }

        $entityManager->persist($commande);
        $entityManager->flush();

        // Clear the cart
        $cartService->clear();

        $this->addFlash('success', 'Commande passée avec succès! Référence: ' . $commande->getReference());

        return $this->redirectToRoute('app_front_index');
    }
}