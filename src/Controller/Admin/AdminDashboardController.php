<?php

namespace App\Controller\Admin;

use App\Entity\Serre;
use App\Entity\Zone;
use App\Entity\User;
use App\Repository\SerreRepository;
use App\Repository\ZoneRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/admin')]
class AdminDashboardController extends AbstractController
{
    private SerreRepository $serreRepository;
    private ZoneRepository $zoneRepository;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        SerreRepository $serreRepository,
        ZoneRepository $zoneRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->serreRepository = $serreRepository;
        $this->zoneRepository = $zoneRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_admin_dashboard')]
    public function dashboard(): Response
    {
        $serres = $this->serreRepository->findAll();
        $zones = $this->zoneRepository->findAll();
        $users = $this->userRepository->findAll();

        $stats = [
            'totalSerres' => count($serres),
            'activeSerres' => count(array_filter($serres, fn($s) => $s->getEtatSerre() === 'actif')),
            'totalZones' => count($zones),
            'activeZones' => count(array_filter($zones, fn($z) => $z->getEtatZone() === 'active')),
            'totalUsers' => count($users),
            'totalSurface' => array_sum(array_map(fn($s) => $s->getSurface(), $serres))
        ];

        // Détecter les incohérences
        $incoherences = $this->detectIncoherences($zones);

        // Activité récente (simulée pour l'instant)
        $recentActivity = $this->getRecentActivity();

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'incoherences' => $incoherences,
            'recentActivity' => $recentActivity
        ]);
    }

    private function detectIncoherences(array $zones): array
    {
        $incoherences = [];
        
        foreach ($zones as $zone) {
            // Zone sans serre
            if (!$zone->getSerre()) {
                $incoherences[] = [
                    'type' => 'zone_sans_serre',
                    'message' => "Zone {$zone->getNomZone()} sans serre associée"
                ];
            }
            
            // Surface zone > surface serre
            if ($zone->getSerre() && $zone->getSuperficie() > $zone->getSerre()->getSurface()) {
                $incoherences[] = [
                    'type' => 'surface_superieure',
                    'message' => "Surface zone ({$zone->getSuperficie()}m²) > surface serre ({$zone->getSerre()->getSurface()}m²)"
                ];
            }
        }
        
        return $incoherences;
    }

    private function getRecentActivity(): array
    {
        // Simuler une activité récente pour l'exemple
        return [
            [
                'createdAt' => new \DateTime('-2 hours'),
                'type' => 'CREATE',
                'entityType' => 'Serre',
                'entityId' => 1,
                'user' => new User(), // Simulé
                'success' => true
            ],
            [
                'createdAt' => new \DateTime('-5 hours'),
                'type' => 'UPDATE',
                'entityType' => 'Zone',
                'entityId' => 2,
                'user' => new User(), // Simulé
                'success' => true
            ],
            [
                'createdAt' => new \DateTime('-1 day'),
                'type' => 'DELETE',
                'entityType' => 'Zone',
                'entityId' => 3,
                'user' => new User(), // Simulé
                'success' => true
            ]
        ];
    }
}
