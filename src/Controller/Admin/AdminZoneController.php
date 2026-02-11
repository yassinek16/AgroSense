<?php

namespace App\Controller\Admin;

use App\Entity\Zone;
use App\Repository\ZoneRepository;
use App\Repository\SerreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/admin')]
class AdminZoneController extends AbstractController
{
    private ZoneRepository $zoneRepository;
    private SerreRepository $serreRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ZoneRepository $zoneRepository,
        SerreRepository $serreRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->zoneRepository = $zoneRepository;
        $this->serreRepository = $serreRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/zones', name: 'app_admin_zones')]
    public function zones(Request $request): Response
    {
        $zones = $this->zoneRepository->findAll();
        $incoherences = $this->detectIncoherences($zones);
        
        $filterEtat = $request->query->get('etat');
        $filterSerre = $request->query->get('serre');

        if ($filterEtat) {
            $zones = array_filter($zones, fn($z) => $z->getEtatZone() === $filterEtat);
        }

        $stats = [
            'total' => count($zones),
            'active' => count(array_filter($zones, fn($z) => $z->getEtatZone() === 'active')),
            'surface' => array_sum(array_map(fn($z) => $z->getSuperficie(), $zones)),
            'incoherences' => count($incoherences)
        ];

        return $this->render('admin/zones_supervision.html.twig', [
            'zones' => $zones,
            'serres' => $this->serreRepository->findAll(),
            'stats' => $stats,
            'incoherences' => $incoherences,
            'filterEtat' => $filterEtat,
            'filterSerre' => $filterSerre
        ]);
    }

    #[Route('/zones/{id}/details', name: 'app_admin_zone_details')]
    public function zoneDetails(Zone $zone): Response
    {
        return $this->render('admin/zone_details.html.twig', ['zone' => $zone]);
    }

    #[Route('/zones/{id}/toggle-state', name: 'app_admin_toggle_zone')]
    public function toggleZoneState(Zone $zone): Response
    {
        $newState = $zone->getEtatZone() === 'active' ? 'inactive' : 'active';
        $zone->setEtatZone($newState);
        $this->entityManager->flush();

        $this->addFlash('success', "Zone {$zone->getNomZone()} mise en {$newState}");
        return $this->redirectToRoute('app_admin_zones');
    }

    #[Route('/zones/{id}/delete', name: 'app_admin_delete_zone')]
    public function deleteZone(Zone $zone, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $this->entityManager->remove($zone);
            $this->entityManager->flush();

            $this->addFlash('success', 'Zone supprimée avec succès');
            return $this->redirectToRoute('app_admin_zones');
        }

        return $this->render('admin/confirm_delete_zone.html.twig', ['zone' => $zone]);
    }

    private function detectIncoherences(array $zones): array
    {
        $incoherences = [];
        
        foreach ($zones as $zone) {
            // Zone sans serre
            if (!$zone->getSerre()) {
                $incoherences[] = [
                    'type' => 'zone_sans_serre',
                    'zone' => $zone,
                    'message' => "Zone {$zone->getNomZone()} sans serre associée"
                ];
            }
            
            // Surface zone > surface serre
            if ($zone->getSerre() && $zone->getSuperficie() > $zone->getSerre()->getSurface()) {
                $incoherences[] = [
                    'type' => 'surface_superieure',
                    'zone' => $zone,
                    'message' => "Surface zone ({$zone->getSuperficie()}m²) > surface serre ({$zone->getSerre()->getSurface()}m²)"
                ];
            }
        }
        
        return $incoherences;
    }
}
