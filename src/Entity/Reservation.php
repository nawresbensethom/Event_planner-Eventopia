<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Evenement;
use App\Entity\Utilisateur;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\Table(name: "reservation")]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_reservation", type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Evenement::class, inversedBy: "reservations")]
    #[ORM\JoinColumn(name: "id_evenement", referencedColumnName: "id_evenement", nullable: false)]
    private ?Evenement $evenement = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: "id_client", referencedColumnName: "id", nullable: false)]
    private ?Utilisateur $client = null;

    #[ORM\Column(type: "float")]
    private float $montant_total = 0.0;

    #[ORM\Column(type: "string", length: 20)]
    private string $statut = 'en_attente';

    #[ORM\Column(type: "json")]
    private array $service_ids = [];

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvenement(): ?Evenement
    {
        return $this->evenement;
    }

    public function setEvenement(?Evenement $evenement): self
    {
        $this->evenement = $evenement;
        return $this;
    }

    public function getClient(): ?Utilisateur
    {
        return $this->client;
    }

    public function setClient(?Utilisateur $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function getMontantTotal(): float
    {
        return $this->montant_total;
    }

    public function setMontantTotal(float $montant_total): self
    {
        $this->montant_total = $montant_total;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getServiceIds(): array
    {
        return $this->service_ids;
    }

    public function setServiceIds(array $service_ids): self
    {
        $this->service_ids = $service_ids;
        return $this;
    }

    public function addServiceId(int $service_id): self
    {
        if (!in_array($service_id, $this->service_ids, true)) {
            $this->service_ids[] = $service_id;
        }
        return $this;
    }

    public function removeServiceId(int $service_id): self
    {
        if (($key = array_search($service_id, $this->service_ids, true)) !== false) {
            unset($this->service_ids[$key]);
            $this->service_ids = array_values($this->service_ids);
        }
        return $this;
    }
}