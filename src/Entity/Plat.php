<?php

namespace App\Entity;

use App\Repository\PlatRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: PlatRepository::class)]
#[Vich\Uploadable()]
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

    #[Vich\UploadableField(mapping: 'plats', fileNameProperty: 'lien_photo')]
    private ?File $imageFile = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updatedAt = null;

    /*
    * Obligatoire pour initialiser l'attribut ci-dessus,
    * (sinon si aucune image ajoutée ou modifiée, sa valeur est à "NULL" et provoque une erreur)
     */
    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine

            // otherwise the event listeners won't be called and the file is lost

            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

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
