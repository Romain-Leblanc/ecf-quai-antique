<?php

namespace App\Entity;

use App\Repository\JourRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JourRepository::class)]
class Jour
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 15)]
    private ?string $libelle = null;

    #[ORM\OneToMany(mappedBy: 'fk_jour', targetEntity: Horaire::class, orphanRemoval: true)]
    private Collection $horaires;

    public function __construct()
    {
        $this->horaires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection<int, Horaire>
     */
    public function getHoraires(): Collection
    {
        return $this->horaires;
    }

    public function addHoraire(Horaire $horaire): self
    {
        if (!$this->horaires->contains($horaire)) {
            $this->horaires->add($horaire);
            $horaire->setFkJour($this);
        }

        return $this;
    }

    public function removeHoraire(Horaire $horaire): self
    {
        if ($this->horaires->removeElement($horaire)) {
            // set the owning side to null (unless already changed)
            if ($horaire->getFkJour() === $this) {
                $horaire->setFkJour(null);
            }
        }

        return $this;
    }

    /* Retourne le libellé du jour */
    public function __toString(): string
    {
        return $this->getLibelle();
    }
}
