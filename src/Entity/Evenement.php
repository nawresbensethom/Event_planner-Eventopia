<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
#[Vich\Uploadable]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: "id")]
    #[Assert\NotNull(message: "L'organisateur est requis.")]
    private ?Utilisateur $id_organisateur = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: "Le nom de l'événement est requis.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le nom de l'événement doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le nom de l'événement ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $nom_evenement = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description est requise.")]
    #[Assert\Length(
        min: 10,
        max: 1000,
        minMessage: "La description doit contenir au moins {{ limit }} caractères.",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date de l'événement est requise.")]
    private ?\DateTimeInterface $date_evenement = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La durée est requise.")]
    #[Assert\GreaterThan(value: 0, message: "La durée doit être supérieure à 0.")]
    private ?int $duree = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rue = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $code_postal = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $longitude = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Assert\All([
        new Assert\Email(message: "Chaque email doit être valide.")
    ])]
    private ?array $liste_invites = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[Vich\UploadableField(mapping: "evenement_images", fileNameProperty: "image")]
    #[Assert\File(
        maxSize: "2M",
        mimeTypes: ["image/jpeg", "image/png", "image/gif"],
        mimeTypesMessage: "Veuillez uploader une image valide (JPEG, PNG, GIF)."
    )]
    private ?File $imageFile = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "La catégorie est requise.")]
    #[Assert\Choice(
        choices: ['concert', 'conférence', 'atelier', 'exposition', 'autre'],
        message: "Choisissez une catégorie valide."
    )]
    private ?string $category = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Le statut de réservation est requis.")]
    #[Assert\Choice(
        choices: ['reserve', 'non reserve'],
        message: "Le statut doit être 'reserve' ou 'non reserve'."
    )]
    private ?string $statut = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Le statut de l'événement est requis.")]
    #[Assert\Choice(
        choices: ['en cours', 'terminé', 'en preparation'],
        message: "Le statut doit être 'en cours', 'terminé' ou 'en preparation'."
    )]
    private ?string $statut2 = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "La visibilité est requise.")]
    #[Assert\Choice(
        choices: ['public', 'privé'],
        message: "La visibilité doit être 'public' ou 'privé'."
    )]
    private ?string $statut3 = null;

    #[ORM\OneToMany(mappedBy: 'evenement', targetEntity: Reservation::class, orphanRemoval: true)]
private Collection $reservations;

    public function __construct()
{
    $this->statut = 'non reserve';
    $this->statut2 = 'en preparation';
    $this->statut3 = 'public'; // Valeur par défaut, mais peut être écrasée par le formulaire
    $this->liste_invites = []; // Initialiser comme tableau vide
    $this->reservations = new ArrayCollection();
}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdOrganisateur(): ?Utilisateur
    {
        return $this->id_organisateur;
    }

    public function setIdOrganisateur(?Utilisateur $id_organisateur): static
    {
        $this->id_organisateur = $id_organisateur;
        return $this;
    }

    public function getNomEvenement(): ?string
    {
        return $this->nom_evenement;
    }

    public function setNomEvenement(string $nom_evenement): static
    {
        $this->nom_evenement = $nom_evenement;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getDateEvenement(): ?\DateTimeInterface
    {
        return $this->date_evenement;
    }

    public function setDateEvenement(\DateTimeInterface $date_evenement): static
    {
        $this->date_evenement = $date_evenement;
        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;
        return $this;
    }

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(?string $rue): static
    {
        $this->rue = $rue;
        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->code_postal;
    }

    public function setCodePostal(?string $code_postal): static
    {
        $this->code_postal = $code_postal;
        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): static
    {
        $this->ville = $ville;
        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getListeInvites(): ?array
    {
        return $this->liste_invites;
    }

    public function setListeInvites(?array $liste_invites): static
    {
        $this->liste_invites = $liste_invites;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile = null): static
    {
        $this->imageFile = $imageFile;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;
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

    public function getStatut2(): ?string
    {
        return $this->statut2;
    }

    public function setStatut2(string $statut2): static
    {
        $this->statut2 = $statut2;
        return $this;
    }

    public function getStatut3(): ?string
    {
        return $this->statut3;
    }

    public function setStatut3(string $statut3): static
    {
        $this->statut3 = $statut3;
        return $this;
    }

    /**
     * Validation personnalisée pour s'assurer que la date de l'événement n'est pas dans le passé.
     */
    #[Assert\Callback]
    public function validateDateEvenement(ExecutionContextInterface $context): void
    {
        if ($this->date_evenement !== null && $this->date_evenement < new \DateTime('today')) {
            $context->buildViolation('La date de l\'événement ne peut pas être dans le passé.')
                ->atPath('date_evenement')
                ->addViolation();
        }
    }
    /**
 * @return Collection<int, Reservation>
 */
public function getReservations(): Collection
{
    return $this->reservations;
}

public function addReservation(Reservation $reservation): static
{
    if (!$this->reservations->contains($reservation)) {
        $this->reservations->add($reservation);
        $reservation->setEvenement($this);
    }
    return $this;
}

public function removeReservation(Reservation $reservation): static
{
    if ($this->reservations->removeElement($reservation)) {
        if ($reservation->getEvenement() === $this) {
            $reservation->setEvenement(null);
        }
    }
    return $this;
}
}