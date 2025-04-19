<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ServiceRepository;
use Doctrine\DBAL\Types\DecimalType;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
#[ORM\Table(name: "service")]
#[ORM\HasLifecycleCallbacks]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_service", type: "integer")]
    private ?int $id_service = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "services")]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "id", nullable: false)]
    #[Assert\NotNull(message: "L'utilisateur est obligatoire")]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: Categorieservice::class, inversedBy: "services")]
    #[ORM\JoinColumn(name: "id_categorie_service", referencedColumnName: "id_categorie_service", nullable: false)]
    #[Assert\NotNull(message: "La catégorie est obligatoire")]
    private ?Categorieservice $categorieService = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le nom du service est obligatoire")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $nom = null;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    #[Assert\Length(
        min: 10,
        max: 1000,
        minMessage: "La description doit contenir au moins {{ limit }} caractères",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $description = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "Le tarif est obligatoire")]
    #[Assert\Positive(message: "Le tarif doit être positif")]
    #[Assert\LessThanOrEqual(value: 999999.99, message: "Le tarif ne peut pas dépasser 999999.99 €")]
    private ?string $tarif; 

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\File(
        maxSize: "2M",
        mimeTypes: ["image/jpeg", "image/png", "image/gif"],
        mimeTypesMessage: "Veuillez télécharger une image valide (JPEG, PNG ou GIF)"
    )]
    private ?string $photos = null;

    #[ORM\Column(name: "date_ajout", type: "datetime", nullable: false)]
    private ?\DateTimeInterface $date_ajout = null;

    #[ORM\PrePersist]
    public function setDateAjoutValue(): void
    {
        $this->date_ajout = new \DateTime();
    }

    public function getIdService(): ?int
    {
        return $this->id_service;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getCategorieService(): ?Categorieservice
    {
        return $this->categorieService;
    }

    public function setCategorieService(?Categorieservice $categorieService): self
    {
        $this->categorieService = $categorieService;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
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

    public function getTarif(): ?float
    {
        return $this->tarif;
    }

    public function setTarif(float $tarif): self
    {
        $this->tarif = $tarif;
        return $this;
    }

    public function getPhotos(): ?string
    {
        return $this->photos;
    }

    public function setPhotos(?string $photos): self
    {
        $this->photos = $photos;
        return $this;
    }

    public function getDateAjout(): ?\DateTimeInterface
    {
        return $this->date_ajout;
    }

    public function setDateAjout(\DateTimeInterface $date_ajout): self
    {
        $this->date_ajout = $date_ajout;
        return $this;
    }
}