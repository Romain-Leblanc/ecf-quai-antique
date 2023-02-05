<?php

namespace App\Entity;

use App\Repository\VisiteurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VisiteurRepository::class)]
class Visiteur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    private ?string $prenom = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column]
    private ?int $nombre_convives = null;

    #[ORM\OneToMany(mappedBy: 'fk_visiteur', targetEntity: Reservation::class)]
    private Collection $reservations;

    #[ORM\OneToMany(mappedBy: 'fk_visiteur', targetEntity: AllergieVisiteur::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $allergieVisiteurs;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
        $this->allergieVisiteurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getNombreConvives(): ?int
    {
        return $this->nombre_convives;
    }

    public function setNombreConvives(int $nombre_convives): self
    {
        $this->nombre_convives = $nombre_convives;

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
            $this->reservations->add($reservation);
            $reservation->setFkVisiteur($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getFkVisiteur() === $this) {
                $reservation->setFkVisiteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AllergieVisiteur>
     */
    public function getAllergieVisiteurs(): Collection
    {
        return $this->allergieVisiteurs;
    }

    public function addAllergieVisiteur(AllergieVisiteur $allergieVisiteur): self
    {
        if (!$this->allergieVisiteurs->contains($allergieVisiteur)) {
            $this->allergieVisiteurs->add($allergieVisiteur);
            $allergieVisiteur->setFkVisiteur($this);
        }

        return $this;
    }

    public function removeAllergieVisiteur(AllergieVisiteur $allergieVisiteur): self
    {
        if ($this->allergieVisiteurs->removeElement($allergieVisiteur)) {
            // set the owning side to null (unless already changed)
            if ($allergieVisiteur->getFkVisiteur() === $this) {
                $allergieVisiteur->setFkVisiteur(null);
            }
        }

        return $this;
    }

    public function getNomComplet(): ?string
    {
        return mb_strtoupper($this->getNom())." ".ucfirst($this->getPrenom());
    }
}
