<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity]
#[ORM\Table(name: "tache")]
class Tache
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 100)]
    #[Assert\NotBlank(message: "Le titre est requis.")]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: "Le titre doit contenir au moins 2 caractères.",
        maxMessage: "Le titre ne doit pas dépasser 100 caractères."
    )]
    private string $titre;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank(message: "La description est requise.")]
    #[Assert\Length(
        min: 10,
        max: 1000,
        minMessage: "La description doit contenir au moins 10 caractères.",
        maxMessage: "La description ne doit pas dépasser 1000 caractères."
    )]
    private string $description;

    #[ORM\Column(type: "date")]
    #[Assert\NotNull(message: "La date est requise.")]
    #[Assert\Type(
        type: "\DateTimeInterface",
        message: "La date doit être une date valide."
    )]
    #[Assert\GreaterThanOrEqual(
        value: "today",
        message: "La date ne doit pas être inférieure à aujourd'hui."
    )]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: "integer")]
    #[Assert\NotNull(message: "La durée estimée est requise.")]
    #[Assert\Positive(message: "La durée estimée doit être un entier positif.")]
    private int $duree_estimee;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "La catégorie est requise.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "La catégorie ne doit pas dépasser 255 caractères."
    )]
    private string $categorie;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le statut est requis.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le statut ne doit pas dépasser 255 caractères."
    )]
    private string $statut;

    #[ORM\ManyToOne(targetEntity: Plan::class, inversedBy: "taches")]
    #[ORM\JoinColumn(name: "id_plan", referencedColumnName: "id", nullable: true)]
    private ?Plan $plan = null;

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

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;
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
    public function getDureeEstimee(): int
    {
        return $this->duree_estimee;
    }

    public function setDureeEstimee(int $duree_estimee): self
    {
        $this->duree_estimee = $duree_estimee;
        return $this;
    }

    public function getCategorie(): string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function getPlan(): ?Plan
    {
        return $this->plan;
    }

    public function setPlan(?Plan $plan): self
    {
        $this->plan = $plan;
        return $this;
    }
}
