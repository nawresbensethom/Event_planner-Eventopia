<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Post;
use App\Entity\Utilisateur;
use App\Entity\Signalement_commentaire;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
#[ORM\Table(name: "commentaire")]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id_commentaire = null;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: "commentaires")]
    #[ORM\JoinColumn(name: "id_post", referencedColumnName: "id_post", onDelete: "CASCADE", nullable: false)]
    private Post $id_post;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "commentaires")]
    #[ORM\JoinColumn(name: "id_utilisateur", referencedColumnName: "id", onDelete: "CASCADE", nullable: false)]
    private Utilisateur $id_utilisateur;

    #[ORM\Column(type: "text")]
    private string $contenu;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_commentaire;

    #[ORM\OneToMany(mappedBy: "id_commentaire", targetEntity: Signalement_commentaire::class, cascade: ["persist", "remove"])]
    private Collection $signalements;

    public function __construct()
    {
        $this->date_commentaire = new \DateTime();
        $this->signalements = new ArrayCollection();
    }

    public function getIdCommentaire(): ?int
    {
        return $this->id_commentaire;
    }

    public function getIdPost(): Post
    {
        return $this->id_post;
    }

    public function setIdPost(Post $id_post): self
    {
        $this->id_post = $id_post;
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

    public function getContenu(): string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getDateCommentaire(): \DateTimeInterface
    {
        return $this->date_commentaire;
    }

    public function setDateCommentaire(\DateTimeInterface $date_commentaire): self
    {
        $this->date_commentaire = $date_commentaire;
        return $this;
    }

    /**
     * @return Collection<int, Signalement_commentaire>
     */
    public function getSignalements(): Collection
    {
        return $this->signalements;
    }

    public function addSignalement(Signalement_commentaire $signalement): self
    {
        if (!$this->signalements->contains($signalement)) {
            $this->signalements->add($signalement);
            $signalement->setIdCommentaire($this);
        }
        return $this;
    }

    public function removeSignalement(Signalement_commentaire $signalement): self
    {
        $this->signalements->removeElement($signalement);
        // Note: We do not call setIdCommentaire(null) because id_commentaire is non-nullable.
        // The caller should handle the actual deletion of the Signalement_commentaire entity.
        return $this;
    }
}