<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use App\Repository\CommandeRepository;
use App\Repository\LigneCommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'app_admin_dashboard')]
    public function dashboard(
        ProduitRepository $produitRepository,
        CommandeRepository $commandeRepository,
        LigneCommandeRepository $ligneCommandeRepository,
        EntityManagerInterface $entityManager
    ): Response {
        
        // Statistiques générales
        $totalProduits = $produitRepository->count([]);
        $totalCommandes = $commandeRepository->count([]);
        $totalVentes = $ligneCommandeRepository->getTotalSales();
        $produitsEnStock = $produitRepository->getTotalStock();
        
        // Produits les plus vendus
        $produitsPlusVendus = $ligneCommandeRepository->getTopSellingProducts(5);
        
        // Commandes récentes
        $commandesRecentes = $commandeRepository->findBy(
            [],
            ['dateCommande' => 'DESC'],
            5
        );
        
        // Produits en rupture de stock
        $produitsEnRupture = $produitRepository->findBy(['quantiteStock' => 0]);
        
        // Chiffre d'affaires par mois (6 derniers mois)
        $chiffreAffairesMensuel = $commandeRepository->getMonthlyRevenue(6);
        
        return $this->render('back/dashboard/index.html.twig', [
            'totalProduits' => $totalProduits,
            'totalCommandes' => $totalCommandes,
            'totalVentes' => $totalVentes,
            'produitsEnStock' => $produitsEnStock,
            'produitsPlusVendus' => $produitsPlusVendus,
            'commandesRecentes' => $commandesRecentes,
            'produitsEnRupture' => $produitsEnRupture,
            'chiffreAffairesMensuel' => $chiffreAffairesMensuel,
        ]);
    }
}
