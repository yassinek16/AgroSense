<?php

namespace App\Controller;

use App\Entity\Serre;
use App\Form\SerreType;
use App\Repository\SerreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/serre')]
final class SerreController extends AbstractController
{
    // Affichage de la liste des serres
  #[Route(name: 'app_serre_index', methods: ['GET'])]
    public function index(Request $request, SerreRepository $serreRepository): Response
    {
        // Récupération des paramètres de l'URL
        $q = $request->query->get('q', '');
        $sort = $request->query->get('sort', 'id');
        $direction = $request->query->get('direction', 'ASC');

        return $this->render('serre/index.html.twig', [
            'serres' => $serreRepository->searchAndSort($q, $sort, $direction),
            'searchTerm' => $q,
            'currentSort' => $sort,
            'currentDirection' => $direction,
        ]);
    }
    // Création d'une nouvelle serre
    #[Route('/new', name: 'app_serre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $serre = new Serre();
        $form = $this->createForm(SerreType::class, $serre);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($serre);
            $entityManager->flush();

            // Redirection vers la liste des serres après l'ajout
            return $this->redirectToRoute('app_serre_index');
        }

        // Affichage du formulaire (avec débogage en cas d'erreur)
        if ($form->isSubmitted() && !$form->isValid()) {
            dump($form->getErrors(true));  // Afficher les erreurs du formulaire pour débogage
        }

        return $this->render('serre/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Affichage des détails d'une serre
    #[Route('/{id}', name: 'app_serre_show', methods: ['GET'])]
    public function show(Serre $serre): Response
    {
        return $this->render('serre/show.html.twig', [
            'serre' => $serre,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_serre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Serre $serre, EntityManagerInterface $entityManager): Response
    {
        // Créer le formulaire avec les données de la serre existante
        $form = $this->createForm(SerreType::class, $serre);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer les modifications dans la base de données
            $entityManager->flush();

            // Message flash de succès
            $this->addFlash('success', 'Serre mise à jour avec succès.');

            // Redirection vers la liste des serres après modification
            return $this->redirectToRoute('app_serre_index', [], Response::HTTP_SEE_OTHER);
        }

        // Affichage du formulaire de modification
        return $this->render('serre/edit.html.twig', [
            'serre' => $serre,
            'form' => $form->createView(),
        ]);
    }
    // Suppression d'une serre
    #[Route('/{id}', name: 'app_serre_delete', methods: ['POST'])]
    public function delete(Request $request, Serre $serre, EntityManagerInterface $entityManager): Response
    {
        // Vérification du token CSRF
        if ($this->isCsrfTokenValid('delete' . $serre->getId(), $request->request->get('_token'))) {
            // Suppression de la serre
            $entityManager->remove($serre);
            $entityManager->flush();
        }

        // Redirection vers la liste des serres après suppression
        return $this->redirectToRoute('app_serre_index', [], Response::HTTP_SEE_OTHER);
    }
}
