<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/evenement')]
class EvenementController extends AbstractController
{
    #[Route('/', name: 'evenement_index', methods: ['GET'])]
    public function index(EvenementRepository $evenementRepository): Response
    {
        // Users see only upcoming events, admins see all
        if ($this->isGranted('ROLE_ADMIN')) {
            $evenements = $evenementRepository->findAll();
        } else {
            $evenements = $evenementRepository->findAvailableEvents();
        }

        return $this->render('evenement/index.html.twig', [
            'evenements' => $evenements,
        ]);
    }

    #[Route('/new', name: 'evenement_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the organizer to the current admin user
            $evenement->setOrganisateur($this->getUser());
            
            $em->persist($evenement);
            $em->flush();

            $this->addFlash('success', 'L\'événement a été créé avec succès.');
            return $this->redirectToRoute('evenement_index');
        }

        return $this->render('evenement/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'evenement_show', methods: ['GET'])]
    public function show(Evenement $evenement, TicketRepository $ticketRepository): Response
    {
        $userTicket = null;
        if ($this->getUser()) {
            $userTicket = $ticketRepository->getUserTicketForEvent($this->getUser(), $evenement);
        }

        return $this->render('evenement/show.html.twig', [
            'evenement' => $evenement,
            'userTicket' => $userTicket,
        ]);
    }

    #[Route('/{id}/edit', name: 'evenement_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            
            $this->addFlash('success', 'L\'événement a été modifié avec succès.');
            return $this->redirectToRoute('evenement_show', ['id' => $evenement->getId()]);
        }

        return $this->render('evenement/edit.html.twig', [
            'form' => $form->createView(),
            'evenement' => $evenement
        ]);
    }

    #[Route('/{id}/delete', name: 'evenement_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$evenement->getId(), $request->request->get('_token'))) {
            $em->remove($evenement);
            $em->flush();
            
            $this->addFlash('success', 'L\'événement a été supprimé avec succès.');
        }

        return $this->redirectToRoute('evenement_index');
    }

    #[Route('/{id}/statistics', name: 'evenement_statistics', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function statistics(Evenement $evenement, TicketRepository $ticketRepository): Response
    {
        $tickets = $ticketRepository->findByEvent($evenement);
        $confirmedTickets = $ticketRepository->findConfirmedByEvent($evenement);
        $revenue = $ticketRepository->getEventRevenue($evenement);

        return $this->render('evenement/statistics.html.twig', [
            'evenement' => $evenement,
            'tickets' => $tickets,
            'confirmedTickets' => $confirmedTickets,
            'revenue' => $revenue,
            'ticketsSold' => count($confirmedTickets),
            'remainingCapacity' => $evenement->getRemainingCapacity(),
        ]);
    }
}
