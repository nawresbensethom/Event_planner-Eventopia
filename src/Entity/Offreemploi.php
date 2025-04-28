<?php

namespace App\Entity;

use App\Repository\OffreemploiRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: OffreemploiRepository::class)]
#[ORM\Table(name: "offreemploi")]
class Offreemploi
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id_offre = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "offreemplois")]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "id", nullable: false)]
    private Utilisateur $id_user;

    #[ORM\Column(type: "string", length: 100)]
    private string $titre_poste;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "string", length: 20)]
    private string $type_contrat;

    #[ORM\Column(type: "string", length: 100)]
    private string $lieu;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, nullable: true)]
    private ?string $salaire = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_limite;

    #[ORM\OneToMany(mappedBy: "offre", targetEntity: Candidature::class, orphanRemoval: true)]
    private Collection $candidatures;

    public function __construct()
    {
        $this->candidatures = new ArrayCollection();
    }

    public function getIdOffre(): ?int
    {
        return $this->id_offre;
    }

    public function getIdUser(): Utilisateur
    {
        return $this->id_user;
    }

    public function setIdUser(Utilisateur $id_user): self
    {
        $this->id_user = $id_user;
        return $this;
    }

    public function getTitrePoste(): string
    {
        return $this->titre_poste;
    }

    public function setTitrePoste(string $titre_poste): self
    {
        $this->titre_poste = $titre_poste;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getTypeContrat(): string
    {
        return $this->type_contrat;
    }

    public function setTypeContrat(string $type_contrat): self
    {
        $this->type_contrat = $type_contrat;
        return $this;
    }

    public function getLieu(): string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): self
    {
        $this->lieu = $lieu;
        return $this;
    }

    public function getSalaire(): ?string
    {
        return $this->salaire;
    }

    public function setSalaire(?string $salaire): self
    {
        $this->salaire = $salaire;
        return $this;
    }

    public function getDateLimite(): \DateTimeInterface
    {
        return $this->date_limite;
    }

    public function setDateLimite(\DateTimeInterface $date_limite): self
    {
        $this->date_limite = $date_limite;
        return $this;
    }

    /** @return Collection<int, Candidature> */
    public function getCandidatures(): Collection
    {
        return $this->candidatures;
    }

    public function addCandidature(Candidature $candidature): self
    {
        if (!$this->candidatures->contains($candidature)) {
            $this->candidatures[] = $candidature;
            $candidature->setOffre($this);
        }

        return $this;
    }

    
}
