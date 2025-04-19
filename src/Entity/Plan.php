<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity]
#[ORM\Table(name: "plan")]
#[Assert\Expression(
    "this.getDateFin() > this.getDateDebut()",
    message: "La date de fin doit être postérieure à la date de début."
)]
class Plan
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    private ?int $id = null;

    // Contrôle de saisie pour le titre : le titre ne doit pas être vide et doit être entre 2 et 30 caractères.
    #[ORM\Column(name: "titre", type: "string", length: 255, nullable: false)]
    #[Assert\NotBlank(message: "Le titre est requis.")]
    #[Assert\Length(
        min: 2,
        max: 30,
        minMessage: "Le titre doit contenir au moins 2 caractères.",
        maxMessage: "Le titre ne doit pas dépasser 30 caractères.",
    )]
    private string $titre;

    // Contrôle de saisie pour la description : la description ne doit pas être vide et doit être entre 10 et 255 caractères.
    #[ORM\Column(name: "description", type: "string", length: 255, nullable: false)]
    #[Assert\NotBlank(message: "La description est requise.")]
    #[Assert\Length(
        min: 10,
        max: 255,
        minMessage: "La description doit contenir au moins 10 caractères.",
        maxMessage: "La description ne doit pas dépasser 255 caractères."
    )]
    private string $description;

    // Contrôle de saisie pour la date de début : la date doit être valide et ne peut pas être dans le futur.
    #[ORM\Column(name: "date_debut", type: "date", nullable: false)]
    #[Assert\NotNull(message: "La date de début est requise.")]
    #[Assert\Type("\DateTimeInterface", message: "La date de début doit être une date valide.")]
    #[Assert\GreaterThanOrEqual(
        value: "today",
        message: "La date de début ne peut pas être dans le passe."
    )]
    private ?\DateTimeInterface $dateDebut;

    // Contrôle de saisie pour la date de fin : la date doit être valide et postérieure à la date de début.
    #[ORM\Column(name: "date_fin", type: "date", nullable: false)]
    #[Assert\NotNull(message: "La date de fin est requise.")]
    #[Assert\Type("\DateTimeInterface", message: "La date de fin doit être une date valide.")]
    private ?\DateTimeInterface $dateFin;

    // Contrôle de saisie pour la priorité : la priorité ne doit pas être vide.
    #[ORM\Column(name: "priorite", type: "string", length: 255, nullable: false)]
    #[Assert\NotBlank(message: "La priorité est requise.")]
    private string $priorite;

    // Contrôle de saisie pour la localisation : la localisation ne doit pas être vide et doit être entre 3 et 255 caractères.
    #[ORM\Column(name: "location", type: "string", length: 255, nullable: false)]
    #[Assert\NotBlank(message: "La localisation est requise.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "La localisation doit contenir au moins 3 caractères.",
        maxMessage: "La localisation ne doit pas dépasser 255 caractères."
    )]
    #[Assert\Regex(
        pattern: '/^[\p{L}\s]+$/u',
        message: "La localisation ne doit contenir que des lettres et des espaces."
    )]
    private string $location;

    #[ORM\OneToMany(targetEntity: Tache::class, mappedBy: "plan", cascade: ["remove"])]
    private Collection $taches;

    public function __construct()
    {
        $this->taches = new ArrayCollection();
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

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getPriorite(): ?string
    {
        return $this->priorite;
    }

    public function setPriorite(?string $priorite): self
    {
        $this->priorite = $priorite;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return Collection<int, Tache>
     */
    public function getTaches(): Collection
    {
        return $this->taches;
    }

    public function addTache(Tache $tache): self
    {
        if (!$this->taches->contains($tache)) {
            $this->taches->add($tache);
            $tache->setPlan($this);
        }

        return $this;
    }

    public function removeTache(Tache $tache): self
    {
        if ($this->taches->removeElement($tache)) {
            if ($tache->getPlan() === $this) {
                $tache->setPlan(null);
            }
        }

        return $this;
    }
}