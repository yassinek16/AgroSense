<?php

namespace App\Controller;

use App\Entity\Zone;
use App\Form\ZoneType;
use App\Repository\ZoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\SerreRepository;

#[Route('/zone')]
final class ZoneController extends AbstractController
{
    #[Route('/serre/{serre_id}', name: 'app_zone_by_serre', methods: ['GET'])]
    public function showBySerre(int $serre_id, ZoneRepository $zoneRepository, SerreRepository $serreRepository): Response
    {
        // Recherche de la serre par son ID
        $serre = $serreRepository->find($serre_id);

        // Si la serre n'existe pas, renvoie une exception 404
        if (!$serre) {
            throw $this->createNotFoundException('Désolé, cette serre n\'existe pas.');
        }

        // Récupère les zones associées à cette serre et les rend dans la vue
        return $this->render('zone/index_by_serre.html.twig', [
            'zones' => $zoneRepository->findBy(['serre' => $serre]),
            'serre' => $serre,
        ]);
    }

    #[Route(name: 'app_zone_index', methods: ['GET'])]
    public function index(ZoneRepository $zoneRepository): Response
    {
        return $this->render('zone/index.html.twig', [
            'zones' => $zoneRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_zone_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $zone = new Zone();
        $form = $this->createForm(ZoneType::class, $zone);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($zone);
            $entityManager->flush();

            return $this->redirectToRoute('app_zone_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('zone/new.html.twig', [
            'zone' => $zone,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_zone_show', methods: ['GET'])]
    public function show(Zone $zone): Response
    {
        return $this->render('zone/show.html.twig', [
            'zone' => $zone,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_zone_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Zone $zone, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ZoneType::class, $zone);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_zone_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('zone/edit.html.twig', [
            'zone' => $zone,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_zone_delete', methods: ['POST'])]
    public function delete(Request $request, Zone $zone, EntityManagerInterface $entityManager): Response
    {
        // Vérification du token CSRF pour éviter les attaques CSRF
        if ($this->isCsrfTokenValid('delete'.$zone->getId(), $request->request->get('_token'))) {
            $entityManager->remove($zone);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_zone_index', [], Response::HTTP_SEE_OTHER);
    }
}
