<?php

namespace App\Entity;

use App\Repository\PlatRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlatRepository::class)]
class Plat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'plats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categorie $fk_categorie = null;

    #[ORM\Column(length: 50)]
    private ?string $titre_plat = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description_plat = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $prix_plat = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lien_photo = null;

    #[ORM\Column]
    private ?bool $afficher_photo = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFkCategorie(): ?Categorie
    {
        return $this->fk_categorie;
    }

    public function setFkCategorie(?Categorie $fk_categorie): self
    {
        $this->fk_categorie = $fk_categorie;

        return $this;
    }

    public function getTitrePlat(): ?string
    {
        return $this->titre_plat;
    }

    public function setTitrePlat(string $titre_plat): self
    {
        $this->titre_plat = $titre_plat;

        return $this;
    }

    public function getDescriptionPlat(): ?string
    {
        return $this->description_plat;
    }

    public function setDescriptionPlat(string $description_plat): self
    {
        $this->description_plat = $description_plat;

        return $this;
    }

    public function getPrixPlat(): ?string
    {
        return $this->prix_plat;
    }

    public function setPrixPlat(string $prix_plat): self
    {
        $this->prix_plat = $prix_plat;

        return $this;
    }

    public function getLienPhoto(): ?string
    {
        return $this->lien_photo;
    }

    public function setLienPhoto(?string $lien_photo): self
    {
        $this->lien_photo = $lien_photo;

        return $this;
    }

    public function isAfficherPhoto(): ?bool
    {
        return $this->afficher_photo;
    }

    public function setAfficherPhoto(bool $afficher_photo): self
    {
        $this->afficher_photo = $afficher_photo;

        return $this;
    }
}
