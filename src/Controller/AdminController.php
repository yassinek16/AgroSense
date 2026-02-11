<?php

namespace App\Controller;

use App\Repository\EvenementRepository;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
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
        EvenementRepository $evenementRepository,
        TicketRepository $ticketRepository,
        UserRepository $userRepository
    ): Response {
        // Get statistics
        $eventStats = $evenementRepository->getEventStatistics();
        $ticketStats = $ticketRepository->getTicketStatistics();
        $totalRevenue = $ticketRepository->getTotalRevenue();
        
        // Get revenue for this month
        $startOfMonth = new \DateTime('first day of this month 00:00:00');
        $endOfMonth = new \DateTime('last day of this month 23:59:59');
        $monthRevenue = $ticketRepository->getRevenueByPeriod($startOfMonth, $endOfMonth);
        
        // Get popular events
        $popularEvents = $evenementRepository->findPopularEvents(5);
        
        // Get recent tickets
        $recentTickets = $ticketRepository->getRecentTickets(10);
        
        // Count total users
        $totalUsers = count($userRepository->findAll());

        return $this->render('admin/dashboard.html.twig', [
            'eventStats' => $eventStats,
            'ticketStats' => $ticketStats,
            'totalRevenue' => $totalRevenue,
            'monthRevenue' => $monthRevenue,
            'popularEvents' => $popularEvents,
            'recentTickets' => $recentTickets,
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
        EvenementRepository $evenementRepository,
        TicketRepository $ticketRepository
    ): Response {
        // Get monthly revenue for the last 6 months
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = new \DateTime("-$i months");
            $start = new \DateTime($date->format('Y-m-01 00:00:00'));
            $end = new \DateTime($date->format('Y-m-t 23:59:59'));
            
            $monthlyRevenue[] = [
                'month' => $date->format('M Y'),
                'revenue' => $ticketRepository->getRevenueByPeriod($start, $end),
            ];
        }

        return $this->render('admin/reports.html.twig', [
            'monthlyRevenue' => $monthlyRevenue,
        ]);
    }
}
