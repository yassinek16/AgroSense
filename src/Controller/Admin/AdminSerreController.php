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
class AdminSerreController extends AbstractController
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

    #[Route('/serres', name: 'app_admin_serres')]
    public function serres(Request $request): Response
    {
        $serres = $this->serreRepository->findAll();
        $users = $this->userRepository->findAll();
        
        $filterEtat = $request->query->get('etat');
        $filterUser = $request->query->get('user');

        if ($filterEtat) {
            $serres = array_filter($serres, fn($s) => $s->getEtatSerre() === $filterEtat);
        }

        $stats = [
            'total' => count($serres),
            'active' => count(array_filter($serres, fn($s) => $s->getEtatSerre() === 'actif')),
            'maintenance' => count(array_filter($serres, fn($s) => $s->getEtatSerre() === 'maintenance')),
            'surface' => array_sum(array_map(fn($s) => $s->getSurface(), $serres))
        ];

        return $this->render('admin/serres_supervision.html.twig', [
            'serres' => $serres,
            'users' => $users,
            'stats' => $stats,
            'filterEtat' => $filterEtat,
            'filterUser' => $filterUser
        ]);
    }

    #[Route('/serres/{id}/details', name: 'app_admin_serre_details')]
    public function serreDetails(Serre $serre): Response
    {
        $zones = $this->zoneRepository->findBy(['serre' => $serre]);
        $surfaceZones = array_sum(array_map(fn($z) => $z->getSuperficie(), $zones));
        
        return $this->render('admin/serre_details.html.twig', [
            'serre' => $serre,
            'zones' => $zones,
            'surfaceZones' => $surfaceZones
        ]);
    }

    #[Route('/serres/{id}/toggle-state', name: 'app_admin_toggle_serre')]
    public function toggleSerreState(Serre $serre): Response
    {
        $newState = $serre->getEtatSerre() === 'actif' ? 'maintenance' : 'actif';
        $serre->setEtatSerre($newState);
        $this->entityManager->flush();

        $this->addFlash('success', "Serre {$serre->getNomSerre()} mise en {$newState}");
        return $this->redirectToRoute('app_admin_serres');
    }

    #[Route('/serres/{id}/delete', name: 'app_admin_delete_serre')]
    public function deleteSerre(Serre $serre, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $this->entityManager->remove($serre);
            $this->entityManager->flush();

            $this->addFlash('success', 'Serre supprimée avec succès');
            return $this->redirectToRoute('app_admin_serres');
        }

        return $this->render('admin/confirm_delete_serre.html.twig', ['serre' => $serre]);
    }
}
