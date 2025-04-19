<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Utilisateur;
use App\Entity\Commentaire;
use App\Entity\SignalementPost;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
#[ORM\Table(name: "post")]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id_post = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "posts")]
    #[ORM\JoinColumn(name: "id_utilisateur", referencedColumnName: "id", onDelete: "CASCADE", nullable: false)]
    private Utilisateur $id_utilisateur;

    #[ORM\Column(type: "string", length: 100)]
    private string $titre;

    #[ORM\Column(type: "text")]
    private string $contenu;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $photos = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_publication;

    #[ORM\Column(type: "string", length: 20)]
    private string $statut;

    #[ORM\OneToMany(mappedBy: "id_post", targetEntity: Commentaire::class, cascade: ["persist", "remove"])]
    private Collection $commentaires;

    #[ORM\OneToMany(mappedBy: "id_post", targetEntity: SignalementPost::class)]
    private Collection $signalements;

    public function __construct()
    {
        $this->commentaires = new ArrayCollection();
        $this->signalements = new ArrayCollection();
        $this->date_publication = new \DateTime();
    }

    public function getIdPost(): ?int
    {
        return $this->id_post;
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

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getContenu(): string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;
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

    public function getDatePublication(): \DateTimeInterface
    {
        return $this->date_publication;
    }

    public function setDatePublication(\DateTimeInterface $date_publication): self
    {
        $this->date_publication = $date_publication;
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

    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): self
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setIdPost($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): self
    {
        $this->commentaires->removeElement($commentaire);
        return $this;
    }

    public function getSignalements(): Collection
    {
        return $this->signalements;
    }

    public function addSignalement(SignalementPost $signalement): self
    {
        if (!$this->signalements->contains($signalement)) {
            $this->signalements->add($signalement);
            $signalement->setIdPost($this);
        }

        return $this;
    }

    public function removeSignalement(SignalementPost $signalement): self
    {
        $this->signalements->removeElement($signalement);
        return $this;
    }
}