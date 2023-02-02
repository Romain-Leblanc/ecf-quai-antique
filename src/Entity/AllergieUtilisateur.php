<?php

namespace App\Entity;

use App\Repository\AllergieUtilisateurRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AllergieUtilisateurRepository::class)]
class AllergieUtilisateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'allergieUtilisateurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $fk_utilisateur = null;

    #[ORM\Column(length: 50)]
    private ?string $allergie = null;

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
