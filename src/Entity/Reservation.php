<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservations', cascade: ['persist'])]
    private ?Utilisateur $fk_utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'reservations', cascade: ['persist'])]
    private ?Visiteur $fk_visiteur = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $heure = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFkUtilisateur(): ?Utilisateur
    {
        return $this->fk_utilisateur;
    }

    public function setFkUtilisateur(?Utilisateur $fk_utilisateur): self
    {
        $this->fk_utilisateur = $fk_utilisateur;

        return $this;
    }

    public function getFkVisiteur(): ?Visiteur
    {
        return $this->fk_visiteur;
    }

    public function setFkVisiteur(?Visiteur $fk_visiteur): self
    {
        $this->fk_visiteur = $fk_visiteur;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getHeure(): ?\DateTimeInterface
    {
        return $this->heure;
    }

    public function setHeure(\DateTimeInterface $heure): self
    {
        $this->heure = $heure;

        return $this;
    }
}
