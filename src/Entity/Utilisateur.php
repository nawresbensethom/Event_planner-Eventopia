<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UtilisateurRepository;
use App\Entity\Offreemploi;
use App\Entity\Post;
use App\Entity\Profil;
use App\Entity\Reservation;
use App\Entity\Service;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\Table(name: "utilisateur")]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le nom ne peut pas être vide")]
    #[Assert\Length(min: 2, max: 255, minMessage: "Le nom doit contenir au moins {{ limit }} caractères")]
    private string $nom;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    #[Assert\NotBlank(message: "L'email ne peut pas être vide")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas un email valide")]
    private string $email;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le mot de passe ne peut pas être vide")]
    #[Assert\Length(min: 8, max: 255, minMessage: "Le mot de passe doit contenir au moins {{ limit }} caractères")]
    private string $mot_de_passe;

    #[ORM\Column(type: "string", length: 20)]
    #[Assert\NotBlank(message: "Le rôle ne peut pas être vide")]
    private string $role;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date_inscription;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $specialite = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $preferences = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $privileges = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $twofa_secret_key = null;

    #[ORM\Column(type: "integer")]
    #[Assert\NotBlank(message: "Le numéro de téléphone ne peut pas être vide")]
    #[Assert\Positive(message: "Le numéro de téléphone doit être positif")]
    #[Assert\Length(exactly: 8, exactMessage: "Le numéro de téléphone doit contenir exactement {{ limit }} chiffres")]
    private int $numtel = 51756897;

    #[ORM\OneToMany(mappedBy: "id_user", targetEntity: Offreemploi::class, cascade: ["persist", "remove"])]
    private Collection $offreemplois;

    #[ORM\OneToMany(mappedBy: "id_utilisateur", targetEntity: Post::class, cascade: ["persist", "remove"])]
    private Collection $posts;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Profil::class, cascade: ['persist', 'remove'])]
    private ?Profil $profil = null;

    #[ORM\OneToMany(mappedBy: "id_client", targetEntity: Reservation::class, cascade: ["persist", "remove"])]
    private Collection $reservations;

    #[ORM\OneToMany(mappedBy: "id_user", targetEntity: Service::class, cascade: ["persist", "remove"])]
    private Collection $services;

    public function __construct()
    {
        $this->offreemplois = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->profil = null;
        $this->reservations = new ArrayCollection();
        $this->services = new ArrayCollection();
        $this->date_inscription = new \DateTime();
    }

    public function getProfil(): ?Profil
    {
        return $this->profil;
    }
    
    public function setProfil(?Profil $profil): self
    {
        // Bidirectional sync
        if ($profil !== null && $profil->getUser() !== $this) {
            $profil->setUser($this);
        }
        $this->profil = $profil;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getMot_de_passe(): string
    {
        return $this->mot_de_passe;
    }

    public function setMot_de_passe(string $mot_de_passe): self
    {
        $this->mot_de_passe = $mot_de_passe;
        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getDate_inscription(): \DateTimeInterface
    {
        return $this->date_inscription;
    }

    public function setDate_inscription(\DateTimeInterface $date_inscription): self
    {
        $this->date_inscription = $date_inscription;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(?string $specialite): self
    {
        $this->specialite = $specialite;
        return $this;
    }

    public function getPreferences(): ?string
    {
        return $this->preferences;
    }

    public function setPreferences(?string $preferences): self
    {
        $this->preferences = $preferences;
        return $this;
    }

    public function getPrivileges(): ?string
    {
        return $this->privileges;
    }

    public function setPrivileges(?string $privileges): self
    {
        $this->privileges = $privileges;
        return $this;
    }

    public function getTwofa_secret_key(): ?string
    {
        return $this->twofa_secret_key;
    }

    public function setTwofa_secret_key(?string $twofa_secret_key): self
    {
        $this->twofa_secret_key = $twofa_secret_key;
        return $this;
    }

    public function getNumtel(): int
    {
        return $this->numtel;
    }

    public function setNumtel(int $numtel): self
    {
        $this->numtel = $numtel;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->mot_de_passe;
    }

    public function getRoles(): array
    {
        return [$this->role]; // Ensure the role is returned as an array
    }
    
    public function getSalt(): ?string
    {
        return null;  // Par défaut, pas de salt nécessaire pour la plupart des encodeurs
    }

    public function eraseCredentials()
    {
        // You can leave this empty if you're not handling sensitive temporary data
    }

    // Relations with other entities
    /**
     * @return Collection<int, Offreemploi>
     */
    public function getOffreemplois(): Collection
    {
        return $this->offreemplois;
    }

    public function addOffreemploi(Offreemploi $offreemploi): self
    {
        if (!$this->offreemplois->contains($offreemploi)) {
            $this->offreemplois[] = $offreemploi;
            $offreemploi->setId_user($this);
        }
        return $this;
    }

    public function removeOffreemploi(Offreemploi $offreemploi): self
    {
        if ($this->offreemplois->removeElement($offreemploi)) {
            if ($offreemploi->getId_user() === $this) {
                $offreemploi->setId_user(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setId_utilisateur($this);
        }
        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            if ($post->getId_utilisateur() === $this) {
                $post->setId_utilisateur(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }
    
    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $reservation->setId_client($this);
        }
        return $this;
    }
    
    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            if ($reservation->getId_client() === $this) {
                $reservation->setId_client(null);
            }
        }
        return $this;
    }
    
    /**
     * @return Collection<int, Service>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }
    
    public function addService(Service $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services[] = $service;
            $service->setId_user($this);
        }
        return $this;
    }
    
    public function removeService(Service $service): self
    {
        if ($this->services->removeElement($service)) {
            if ($service->getId_user() === $this) {
                $service->setId_user(null);
            }
        }
        return $this;
    }
}