<?php

namespace App\Entity;

use App\Repository\CandidatureRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CandidatureRepository::class)]
#[ORM\Table(name: "candidature")]
class Candidature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id_candidature;

    #[ORM\ManyToOne(inversedBy: "candidatures")]
    #[ORM\JoinColumn(name: "id_offre", referencedColumnName: "id_offre", nullable: false, onDelete: "CASCADE")]
    private OffreEmploi $offre;

    #[ORM\ManyToOne(inversedBy: "candidatures")]
    #[ORM\JoinColumn(name: "id_prestataire", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private Utilisateur $prestataire;

    #[ORM\Column(type: "text")]
    private string $cv;

    #[ORM\Column(type: "text")]
    private string $lettre_motivation;

    #[ORM\Column(type: "string", length: 20)]
    private string $statut;

    public function getIdCandidature(): int
    {
        return $this->id_candidature;
    }

    public function getOffre(): OffreEmploi
    {
        return $this->offre;
    }

    public function setOffre(OffreEmploi $offre): self
    {
        $this->offre = $offre;
        return $this;
    }

    public function getPrestataire(): Utilisateur
    {
        return $this->prestataire;
    }

    public function setPrestataire(Utilisateur $prestataire): self
    {
        $this->prestataire = $prestataire;
        return $this;
    }

    public function getCv(): string
    {
        return $this->cv;
    }

    public function setCv(string $cv): self
    {
        $this->cv = $cv;
        return $this;
    }

    public function getLettreMotivation(): string
    {
        return $this->lettre_motivation;
    }

    public function setLettreMotivation(string $lettre_motivation): self
    {
        $this->lettre_motivation = $lettre_motivation;
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
