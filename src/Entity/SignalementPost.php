<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Post;
use App\Entity\Utilisateur;
use App\Repository\SignalementPostRepository;

#[ORM\Entity(repositoryClass: SignalementPostRepository::class)]
#[ORM\Table(name: "signalement_post")]
class SignalementPost
{
    public const STATUS_EN_ATTENTE = 'En attente';
    public const STATUS_TRAITE = 'Traité';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id_signalement_post = null;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: "signalements")]
    #[ORM\JoinColumn(name: "id_post", referencedColumnName: "id_post", onDelete: "CASCADE", nullable: false)]
    private ?Post $id_post = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "signalementsPosts")]
    #[ORM\JoinColumn(name: "id_utilisateur", referencedColumnName: "id", onDelete: "CASCADE", nullable: false)]
    private ?Utilisateur $idUtilisateur = null;

    #[ORM\Column(type: "text")]
    private string $raison;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_signalement;

    #[ORM\Column(type: "string", length: 20)]
    private string $statut = "En attente";

    public function __construct()
    {
        $this->date_signalement = new \DateTime();
    }

    public function getIdSignalementPost(): ?int
    {
        return $this->id_signalement_post;
    }

    public function getId_signalement_post(): ?int
    {
        return $this->id_signalement_post;
    }

    public function getIdPost(): ?Post
    {
        return $this->id_post;
    }

    public function setIdPost(?Post $id_post): self
    {
        $this->id_post = $id_post;
        return $this;
    }

    public function getId_post(): ?Post
    {
        return $this->id_post;
    }

    public function setId_post(?Post $id_post): self
    {
        $this->id_post = $id_post;
        return $this;
    }

    public function getIdUtilisateur(): ?Utilisateur
    {
        return $this->idUtilisateur;
    }

    public function setIdUtilisateur(?Utilisateur $idUtilisateur): self
    {
        $this->idUtilisateur = $idUtilisateur;
        return $this;
    }

    public function getId_utilisateur(): ?Utilisateur
    {
        return $this->idUtilisateur;
    }

    public function setId_utilisateur(?Utilisateur $idUtilisateur): self
    {
        $this->idUtilisateur = $idUtilisateur;
        return $this;
    }

    public function getRaison(): string
    {
        return $this->raison;
    }

    public function setRaison(string $raison): self
    {
        $this->raison = $raison;
        return $this;
    }

    public function getDateSignalement(): \DateTimeInterface
    {
        return $this->date_signalement;
    }

    public function getDate_signalement(): \DateTimeInterface
    {
        return $this->date_signalement;
    }

    public function setDateSignalement(\DateTimeInterface $date_signalement): self
    {
        $this->date_signalement = $date_signalement;
        return $this;
    }

    public function setDate_signalement(\DateTimeInterface $date_signalement): self
    {
        $this->date_signalement = $date_signalement;
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
}