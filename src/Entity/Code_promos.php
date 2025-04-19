<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\Code_promosRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: Code_promosRepository::class)]
#[ORM\Table(name: "code_promos")]
#[UniqueEntity(fields: ["code"], message: "Ce code promotionnel existe déjà")]
class Code_promos
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 50)]
    #[Assert\NotBlank(message: "Le code est obligatoire")]
    #[Assert\Length(
        min: 5,
        max: 50,
        minMessage: "Le code doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le code ne peut pas dépasser {{ limit }} caractères"
    )]
    #[Assert\Regex(
        pattern: "/^[A-Z0-9-]+$/",
        message: "Le code ne peut contenir que des lettres majuscules, des chiffres ou des tirets"
    )]
    private string $code = '';

    #[ORM\Column(type: "text", nullable: true)]
    #[Assert\Length(
        max: 500,
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $description = null;

    #[ORM\Column(name: "reduction_type", type: "string", length: 100, nullable: true)]
    #[Assert\Choice(
        choices: ["fixed", "percentage"],
        message: "Le type de réduction doit être soit 'fixed' soit 'percentage'"
    )]
    private ?string $reductionType = null;

    #[ORM\Column(name: "date_debut", type: "date")]
    #[Assert\NotNull(message: "La date de début est obligatoire")]
    #[Assert\GreaterThanOrEqual(
        value: "today",
        message: "La date de début doit être aujourd'hui ou dans le futur"
    )]
    private \DateTimeInterface $dateDebut;

    #[ORM\Column(name: "date_expiration", type: "date")]
    #[Assert\NotNull(message: "La date d'expiration est obligatoire")]
    #[Assert\GreaterThan(
        propertyPath: "dateDebut",
        message: "La date d'expiration doit être postérieure à la date de début"
    )]
    #[Assert\LessThanOrEqual(
        value: "+1 year",
        message: "La date d'expiration ne peut pas dépasser un an à partir d'aujourd'hui"
    )]
    private \DateTimeInterface $dateExpiration;

    #[ORM\Column(name: "limite_utilisation", type: "integer", nullable: true)]
    #[Assert\PositiveOrZero(message: "La limite d'utilisation doit être un nombre positif ou zéro")]
    #[Assert\LessThanOrEqual(
        value: 1000,
        message: "La limite d'utilisation ne peut pas dépasser 1000"
    )]
    private ?int $limiteUtilisation = null;

    #[ORM\ManyToOne(targetEntity: Service::class)]
    #[ORM\JoinColumn(name: "service_id", referencedColumnName: "id_service", onDelete: "SET NULL")]
    private ?Service $service = null;

    #[ORM\Column(name: "created_at", type: "datetime")]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(name: "updated_at", type: "datetime")]
    private \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->dateDebut = new \DateTime();
        $this->dateExpiration = new \DateTime();
    }

    // Getters/Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
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

    public function getReductionType(): ?string
    {
        return $this->reductionType;
    }

    public function setReductionType(?string $reductionType): self
    {
        $this->reductionType = $reductionType;
        return $this;
    }

    public function getDateDebut(): \DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateExpiration(): \DateTimeInterface
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(\DateTimeInterface $dateExpiration): self
    {
        $this->dateExpiration = $dateExpiration;
        return $this;
    }

    public function getLimiteUtilisation(): ?int
    {
        return $this->limiteUtilisation;
    }

    public function setLimiteUtilisation(?int $limiteUtilisation): self
    {
        $this->limiteUtilisation = $limiteUtilisation;
        return $this;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateTimestamps(): void
    {
        $this->updatedAt = new \DateTime();
        
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTime();
        }
    }
}