<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\Column(length: 100)]
    private ?string $typeEvenement = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(length: 255)]
    private ?string $lieu = null;

    #[ORM\Column(nullable: true)]
    private ?int $capaciteMax = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = 'prevu';

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $ticketPrice = '0.00';

    #[ORM\Column(type: 'boolean')]
    private bool $requiresTicket = false;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'organizedEvents')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $organisateur = null;

    /**
     * @var Collection<int, Ticket>
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'evenement', orphanRemoval: true)]
    private Collection $tickets;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->tickets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getTypeEvenement(): ?string
    {
        return $this->typeEvenement;
    }

    public function setTypeEvenement(string $typeEvenement): self
    {
        $this->typeEvenement = $typeEvenement;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): self
    {
        $this->lieu = $lieu;
        return $this;
    }

    public function getCapaciteMax(): ?int
    {
        return $this->capaciteMax;
    }

    public function setCapaciteMax(?int $capaciteMax): self
    {
        $this->capaciteMax = $capaciteMax;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getTicketPrice(): ?string
    {
        return $this->ticketPrice;
    }

    public function setTicketPrice(string $ticketPrice): self
    {
        $this->ticketPrice = $ticketPrice;
        return $this;
    }

    public function isRequiresTicket(): bool
    {
        return $this->requiresTicket;
    }

    public function setRequiresTicket(bool $requiresTicket): self
    {
        $this->requiresTicket = $requiresTicket;
        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    public function getOrganisateur(): ?User
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?User $organisateur): self
    {
        $this->organisateur = $organisateur;
        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): static
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets->add($ticket);
            $ticket->setEvenement($this);
        }
        return $this;
    }

    public function removeTicket(Ticket $ticket): static
    {
        if ($this->tickets->removeElement($ticket)) {
            if ($ticket->getEvenement() === $this) {
                $ticket->setEvenement(null);
            }
        }
        return $this;
    }

    public function getTicketsSold(): int
    {
        return $this->tickets->filter(function(Ticket $ticket) {
            return $ticket->getStatut() === 'confirmed';
        })->count();
    }

    public function getRemainingCapacity(): ?int
    {
        if ($this->capaciteMax === null) {
            return null;
        }
        return $this->capaciteMax - $this->getTicketsSold();
    }

    public function isFull(): bool
    {
        if ($this->capaciteMax === null) {
            return false;
        }
        return $this->getTicketsSold() >= $this->capaciteMax;
    }

    public function getTotalRevenue(): float
    {
        $total = 0;
        foreach ($this->tickets as $ticket) {
            if ($ticket->getStatut() === 'confirmed') {
                $total += (float) $ticket->getPrixPaye();
            }
        }
        return $total;
    }

    public function isUpcoming(): bool
    {
        return $this->dateDebut > new \DateTime();
    }

    public function isPast(): bool
    {
        return $this->dateFin < new \DateTime();
    }

    public function isOngoing(): bool
    {
        $now = new \DateTime();
        return $this->dateDebut <= $now && $this->dateFin >= $now;
    }
}
