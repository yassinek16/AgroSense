<?php

namespace App\Controller;

use App\Repository\EvenementRepository;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use App\Repository\ProduitRepository;
use App\Repository\CommandeRepository;
use App\Repository\LigneCommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(
        ProduitRepository $produitRepository,
        CommandeRepository $commandeRepository,
        LigneCommandeRepository $ligneCommandeRepository,
        EvenementRepository $evenementRepository,
        TicketRepository $ticketRepository,
        UserRepository $userRepository
    ): Response {
        // === PRODUCTS & ORDERS STATS ===
        $totalProduits = $produitRepository->count([]);
        $totalCommandes = $commandeRepository->count([]);
        $totalVentes = $ligneCommandeRepository->getTotalSales();
        $produitsEnStock = $produitRepository->getTotalStock();
        $produitsPlusVendus = $ligneCommandeRepository->getTopSellingProducts(5);
        $commandesRecentes = $commandeRepository->findBy(
            [],
            ['dateCommande' => 'DESC'],
            5
        );
        $produitsEnRupture = $produitRepository->findBy(['quantiteStock' => 0]);
        $chiffreAffairesMensuel = $commandeRepository->getMonthlyRevenue(6);

        // === EVENTS & TICKETS STATS ===
        $eventStats = $evenementRepository->getEventStatistics();
        $ticketStats = $ticketRepository->getTicketStatistics();
        $totalRevenue = $ticketRepository->getTotalRevenue();
        
        $startOfMonth = new \DateTime('first day of this month 00:00:00');
        $endOfMonth = new \DateTime('last day of this month 23:59:59');
        $monthRevenue = $ticketRepository->getRevenueByPeriod($startOfMonth, $endOfMonth);
        
        $popularEvents = $evenementRepository->findPopularEvents(5);
        $recentTickets = $ticketRepository->getRecentTickets(10);

        // === USERS STATS ===
        $totalUsers = count($userRepository->findAll());

        return $this->render('admin/dashboard.html.twig', [
            // Products & Orders
            'totalProduits' => $totalProduits,
            'totalCommandes' => $totalCommandes,
            'totalVentes' => $totalVentes,
            'produitsEnStock' => $produitsEnStock,
            'produitsPlusVendus' => $produitsPlusVendus,
            'commandesRecentes' => $commandesRecentes,
            'produitsEnRupture' => $produitsEnRupture,
            'chiffreAffairesMensuel' => $chiffreAffairesMensuel,
            // Events & Tickets
            'eventStats' => $eventStats,
            'ticketStats' => $ticketStats,
            'totalRevenue' => $totalRevenue,
            'monthRevenue' => $monthRevenue,
            'popularEvents' => $popularEvents,
            'recentTickets' => $recentTickets,
            // Users
            'totalUsers' => $totalUsers,
        ]);
    }

    #[Route('/users', name: 'admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/tickets', name: 'admin_tickets')]
    public function tickets(TicketRepository $ticketRepository): Response
    {
        $tickets = $ticketRepository->findAll();

        return $this->render('admin/tickets.html.twig', [
            'tickets' => $tickets,
        ]);
    }

    #[Route('/reports', name: 'admin_reports')]
    public function reports(
        CommandeRepository $commandeRepository,
        EvenementRepository $evenementRepository,
        TicketRepository $ticketRepository
    ): Response {
        // === PRODUCTS & ORDERS REPORTS ===
        $monthlyOrderRevenue = $commandeRepository->getMonthlyRevenue(6);

        // === EVENTS & TICKETS REPORTS ===
        $monthlyTicketRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = new \DateTime("-$i months");
            $start = new \DateTime($date->format('Y-m-01 00:00:00'));
            $end = new \DateTime($date->format('Y-m-t 23:59:59'));
            
            $monthlyTicketRevenue[] = [
                'month' => $date->format('M Y'),
                'revenue' => $ticketRepository->getRevenueByPeriod($start, $end),
            ];
        }

        return $this->render('admin/reports.html.twig', [
            'monthlyOrderRevenue' => $monthlyOrderRevenue,
            'monthlyTicketRevenue' => $monthlyTicketRevenue,
        ]);
    }
}
