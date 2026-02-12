<?php

namespace App\Entity;

use App\Repository\SerreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SerreRepository::class)]
class Serre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
#[ORM\Column(name: 'nom_serre', type: 'string', length: 255, nullable: false)]
#[Assert\NotBlank(message: "Le nom de la serre est obligatoire")]
private ?string $nomSerre = null;

#[ORM\Column(name: 'localisation', type: 'string', length: 255, nullable: false)]
#[Assert\NotBlank(message: "La localisation est obligatoire")]
private ?string $localisation = null;

#[ORM\Column(name: 'surface', type: 'float', nullable: false)]
#[Assert\NotNull(message: "La surface est obligatoire")]
#[Assert\Positive(message: "La surface doit être positive")]
private ?float $surface = null;

#[ORM\Column(name: 'etat_serre', type: 'string', length: 255, nullable: false)]
#[Assert\NotBlank(message: "L'état de la serre est obligatoire")]
private ?string $etatSerre = 'inactif';

#[ORM\Column(type: 'date', nullable: true)]
#[Assert\NotNull(message: 'La date de mise en service est obligatoire')]
private ?\DateTimeInterface $dateMiseEnService = null;

    /**
     * @var Collection<int, Zone>
     */
    #[ORM\OneToMany(targetEntity: Zone::class, mappedBy: 'serre')]
    private Collection $zones;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'serres')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    public function __construct()
    {
        $this->zones = new ArrayCollection();
    }

    /* ================= GETTERS & SETTERS ================= */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomSerre(): ?string
    {
        return $this->nomSerre;
    }

    public function setNomSerre(string $nomSerre): static
    {
        $this->nomSerre = $nomSerre;
        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): static
    {
        $this->localisation = $localisation;
        return $this;
    }

    public function getSurface(): ?float
    {
        return $this->surface;
    }

    public function setSurface(float $surface): static
    {
        $this->surface = $surface;
        return $this;
    }

    public function getEtatSerre(): ?string
    {
        return $this->etatSerre;
    }

    public function setEtatSerre(string $etatSerre): static
    {
        $this->etatSerre = $etatSerre;
        return $this;
    }

 public function getDateMiseEnService(): ?\DateTimeInterface
{
    return $this->dateMiseEnService;
}

public function setDateMiseEnService(?\DateTimeInterface $dateMiseEnService): static
{
    $this->dateMiseEnService = $dateMiseEnService;
    return $this;
}



    /**
     * @return Collection<int, Zone>
     */
    public function getZones(): Collection
    {
        return $this->zones;
    }

    public function addZone(Zone $zone): static
    {
        if (!$this->zones->contains($zone)) {
            $this->zones->add($zone);
            $zone->setSerre($this);
        }

        return $this;
    }

    public function removeZone(Zone $zone): static
    {
        if ($this->zones->removeElement($zone)) {
            if ($zone->getSerre() === $this) {
                $zone->setSerre(null);
            }
        }

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
