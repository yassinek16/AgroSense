<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Evenement::class, inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Evenement $evenement = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $prixPaye = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateAchat = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = 'pending';

    #[ORM\Column(length: 100, unique: true)]
    private ?string $referenceTicket = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateValidation = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $paymentMethod = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $transactionId = null;

    public function __construct()
    {
        $this->dateAchat = new \DateTime();
        $this->referenceTicket = $this->generateReference();
    }

    private function generateReference(): string
    {
        return 'TKT-' . strtoupper(uniqid());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getEvenement(): ?Evenement
    {
        return $this->evenement;
    }

    public function setEvenement(?Evenement $evenement): static
    {
        $this->evenement = $evenement;
        return $this;
    }

    public function getPrixPaye(): ?string
    {
        return $this->prixPaye;
    }

    public function setPrixPaye(string $prixPaye): static
    {
        $this->prixPaye = $prixPaye;
        return $this;
    }

    public function getDateAchat(): ?\DateTimeInterface
    {
        return $this->dateAchat;
    }

    public function setDateAchat(\DateTimeInterface $dateAchat): static
    {
        $this->dateAchat = $dateAchat;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getReferenceTicket(): ?string
    {
        return $this->referenceTicket;
    }

    public function setReferenceTicket(string $referenceTicket): static
    {
        $this->referenceTicket = $referenceTicket;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getDateValidation(): ?\DateTimeInterface
    {
        return $this->dateValidation;
    }

    public function setDateValidation(?\DateTimeInterface $dateValidation): static
    {
        $this->dateValidation = $dateValidation;
        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?string $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function setTransactionId(?string $transactionId): static
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    public function confirm(): void
    {
        $this->statut = 'confirmed';
        $this->dateValidation = new \DateTime();
    }

    public function cancel(): void
    {
        $this->statut = 'cancelled';
    }

    public function isConfirmed(): bool
    {
        return $this->statut === 'confirmed';
    }

    public function isPending(): bool
    {
        return $this->statut === 'pending';
    }

    public function isCancelled(): bool
    {
        return $this->statut === 'cancelled';
    }
}
