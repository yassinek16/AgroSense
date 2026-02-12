<?php

namespace App\Entity;

use App\Repository\ZoneRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ZoneRepository::class)]
class Zone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de la zone est obligatoire")]
    #[Assert\Length(
        min: 3,
        minMessage: "Le nom de la zone doit contenir au moins 3 caractères"
    )]
    private ?string $nomZone = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type de zone est obligatoire")]
    private ?string $typeZone = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La superficie est obligatoire")]
    #[Assert\Positive(message: "La superficie doit être un nombre positif")]
    private ?float $superficie = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'état de la zone est obligatoire")]
    private ?string $etatZone = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La culture associée est obligatoire")]
    private ?string $cultureAssociee = null;

    #[ORM\ManyToOne(inversedBy: 'zones')]
    #[ORM\JoinColumn(name: 'serre_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Serre $serre = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'zones')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    

    /* ================= GETTERS & SETTERS ================= */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomZone(): ?string
    {
        return $this->nomZone;
    }

    public function setNomZone(string $nomZone): static
    {
        $this->nomZone = $nomZone;
        return $this;
    }

    public function getTypeZone(): ?string
    {
        return $this->typeZone;
    }

    public function setTypeZone(string $typeZone): static
    {
        $this->typeZone = $typeZone;
        return $this;
    }

    public function getSuperficie(): ?float
    {
        return $this->superficie;
    }

    public function setSuperficie(float $superficie): static
    {
        $this->superficie = $superficie;
        return $this;
    }

    public function getEtatZone(): ?string
    {
        return $this->etatZone;
    }

    public function setEtatZone(string $etatZone): static
    {
        $this->etatZone = $etatZone;
        return $this;
    }

    public function getCultureAssociee(): ?string
    {
        return $this->cultureAssociee;
    }

    public function setCultureAssociee(string $cultureAssociee): static
    {
        $this->cultureAssociee = $cultureAssociee;
        return $this;
    }

    public function getSerre(): ?Serre
    {
        return $this->serre;
    }

    public function setSerre(?Serre $serre): static
    {
        $this->serre = $serre;
        return $this;
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
}
