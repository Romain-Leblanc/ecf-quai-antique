<?php

namespace App\Entity;

use App\Repository\AllergieVisiteurRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AllergieVisiteurRepository::class)]
class AllergieVisiteur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'allergieVisiteurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Visiteur $fk_visiteur = null;

    #[ORM\Column(length: 50)]
    private ?string $allergie = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAllergie(): ?string
    {
        return $this->allergie;
    }

    public function setAllergie(string $allergie): self
    {
        $this->allergie = $allergie;

        return $this;
    }
}
