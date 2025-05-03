<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[UniqueEntity(fields: ['id'], message: 'Une réservation avec cet ID existe déjà.')]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "L'événement est requis.")]
    private ?Evenement $evenement = null;

    #[ORM\ManyToMany(targetEntity: Service::class)]
    #[ORM\JoinTable(name: 'reservation_service')]
    #[ORM\JoinColumn(name: 'reservation_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'service_id', referencedColumnName: 'id_service')]
    #[Assert\NotBlank(message: "Au moins un service doit être sélectionné.")]
    private Collection $services;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\GreaterThanOrEqual(value: 0, message: "Le montant total doit être positif ou nul.")]
    private ?string $montantTotal = '0.00';

    #[ORM\ManyToOne(targetEntity: Code_promos::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Code_promos $codePromos = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(
        choices: ['paye', 'non paye'],
        message: "Le statut doit être 'paye' ou 'non paye'."
    )]
    private ?string $statut = 'non paye';

    public function __construct()
    {
        $this->services = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, Service>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): static
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
            $this->updateMontantTotal();
        }
        return $this;
    }

    public function removeService(Service $service): static
    {
        $this->services->removeElement($service);
        $this->updateMontantTotal();
        return $this;
    }

    public function getMontantTotal(): ?string
    {
        return $this->montantTotal;
    }

    public function setMontantTotal(string $montantTotal): static
    {
        $this->montantTotal = $montantTotal;
        return $this;
    }

    public function updateMontantTotal(): static
    {
        $total = 0.00;
        foreach ($this->services as $service) {
            $total += (float) $service->getTarif();
        }
        $this->montantTotal = number_format($total, 2, '.', '');
        return $this;
    }

    public function getCodePromos(): ?Code_promos
    {
        return $this->codePromos;
    }

    public function setCodePromos(?Code_promos $codePromos): static
    {
        $this->codePromos = $codePromos;
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
}