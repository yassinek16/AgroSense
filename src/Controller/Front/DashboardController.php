<?php

namespace App\Controller\Front;

use App\Repository\SerreRepository;
use App\Repository\ZoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/front')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_front_dashboard')]
    public function index(SerreRepository $serreRepo, ZoneRepository $zoneRepo): Response
    {
        $nombreSerres = $serreRepo->count([]);
        $nombreZones = $zoneRepo->count([]);

        $serres = $serreRepo->findAll();
        $surfaceTotale = 0;

        foreach ($serres as $serre) {
            $surfaceTotale += $serre->getSurface();
        }

        return $this->render('front/dashboard/index.html.twig', [
            'nombreSerres' => $nombreSerres,
            'nombreZones' => $nombreZones,
            'surfaceTotale' => $surfaceTotale,
        ]);
    }
}
