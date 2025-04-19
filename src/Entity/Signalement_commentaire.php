<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Commentaire;
use App\Entity\Utilisateur;

#[ORM\Entity]
#[ORM\Table(name: "signalement_commentaire")]
class Signalement_commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id_signalement_commentaire = null;

    #[ORM\ManyToOne(targetEntity: Commentaire::class, inversedBy: "signalements")]
    #[ORM\JoinColumn(name: "id_commentaire", referencedColumnName: "id_commentaire", onDelete: "CASCADE", nullable: false)]
    private Commentaire $id_commentaire;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "signalementsCommentaires")]
    #[ORM\JoinColumn(name: "id_utilisateur", referencedColumnName: "id", onDelete: "CASCADE", nullable: false)]
    private Utilisateur $id_utilisateur;

    #[ORM\Column(type: "text")]
    private string $raison;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_signalement;

    public function __construct()
    {
        $this->date_signalement = new \DateTime();
    }

    public function getIdSignalementCommentaire(): ?int
    {
        return $this->id_signalement_commentaire;
    }

    public function getIdCommentaire(): Commentaire
    {
        return $this->id_commentaire;
    }

    public function setIdCommentaire(Commentaire $id_commentaire): self
    {
        $this->id_commentaire = $id_commentaire;
        return $this;
    }

    public function getIdUtilisateur(): Utilisateur
    {
        return $this->id_utilisateur;
    }

    public function setIdUtilisateur(Utilisateur $id_utilisateur): self
    {
        $this->id_utilisateur = $id_utilisateur;
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

    public function setDateSignalement(\DateTimeInterface $date_signalement): self
    {
        $this->date_signalement = $date_signalement;
        return $this;
    }
}