<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\Ticket;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/ticket')]
#[IsGranted('ROLE_USER')]
class TicketController extends AbstractController
{
    #[Route('/my-tickets', name: 'ticket_my_tickets', methods: ['GET'])]
    public function myTickets(TicketRepository $ticketRepository): Response
    {
        $tickets = $ticketRepository->findByUser($this->getUser());

        return $this->render('ticket/my_tickets.html.twig', [
            'tickets' => $tickets,
        ]);
    }

    #[Route('/purchase/{id}', name: 'ticket_purchase', methods: ['GET', 'POST'])]
    public function purchase(
        Request $request,
        Evenement $evenement,
        TicketRepository $ticketRepository,
        EntityManagerInterface $em
    ): Response {
        // Check if event requires tickets
        if (!$evenement->isRequiresTicket()) {
            $this->addFlash('error', 'Cet événement ne nécessite pas de billet.');
            return $this->redirectToRoute('evenement_show', ['id' => $evenement->getId()]);
        }

        // Check if event is full
        if ($evenement->isFull()) {
            $this->addFlash('error', 'Cet événement est complet.');
            return $this->redirectToRoute('evenement_show', ['id' => $evenement->getId()]);
        }

        // Check if user already has a ticket for this event
        $existingTicket = $ticketRepository->getUserTicketForEvent($this->getUser(), $evenement);
        if ($existingTicket) {
            $this->addFlash('warning', 'Vous avez déjà un billet pour cet événement.');
            return $this->redirectToRoute('ticket_my_tickets');
        }

        if ($request->isMethod('POST')) {
            $ticket = new Ticket();
            $ticket->setUser($this->getUser());
            $ticket->setEvenement($evenement);
            $ticket->setPrixPaye($evenement->getTicketPrice());
            $ticket->setPaymentMethod($request->request->get('payment_method', 'cash'));
            
            // For demonstration, we'll auto-confirm. In production, integrate payment gateway
            $ticket->confirm();
            
            $em->persist($ticket);
            $em->flush();

            $this->addFlash('success', 'Votre billet a été acheté avec succès! Référence: ' . $ticket->getReferenceTicket());
            return $this->redirectToRoute('ticket_show', ['id' => $ticket->getId()]);
        }

        return $this->render('ticket/purchase.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/{id}', name: 'ticket_show', methods: ['GET'])]
    public function show(Ticket $ticket): Response
    {
        // Ensure user can only see their own tickets (or admin can see all)
        if ($ticket->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket,
        ]);
    }

    #[Route('/{id}/cancel', name: 'ticket_cancel', methods: ['POST'])]
    public function cancel(Request $request, Ticket $ticket, EntityManagerInterface $em): Response
    {
        // Ensure user can only cancel their own tickets
        if ($ticket->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Check if event hasn't started yet
        if ($ticket->getEvenement()->getDateDebut() < new \DateTime()) {
            $this->addFlash('error', 'Vous ne pouvez pas annuler un billet pour un événement déjà commencé.');
            return $this->redirectToRoute('ticket_show', ['id' => $ticket->getId()]);
        }

        if ($this->isCsrfTokenValid('cancel'.$ticket->getId(), $request->request->get('_token'))) {
            $ticket->cancel();
            $em->flush();
            
            $this->addFlash('success', 'Votre billet a été annulé.');
        }

        return $this->redirectToRoute('ticket_my_tickets');
    }

    #[Route('/{id}/download', name: 'ticket_download', methods: ['GET'])]
    public function download(Ticket $ticket): Response
    {
        // Ensure user can only download their own tickets (or admin can download all)
        if ($ticket->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        // Here you would generate a PDF ticket
        // For now, we'll just show the ticket page
        return $this->render('ticket/download.html.twig', [
            'ticket' => $ticket,
        ]);
    }
}
