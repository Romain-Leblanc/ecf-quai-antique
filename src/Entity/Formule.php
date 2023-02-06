<?php

namespace App\Entity;

use App\Repository\FormuleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormuleRepository::class)]
class Formule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $titre_formule = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description_formule = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $prix_formule = null;

    #[ORM\ManyToMany(targetEntity: Menu::class, mappedBy: 'formules')]
    private Collection $menus;

    public function __construct()
    {
        $this->menus = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitreFormule(): ?string
    {
        return $this->titre_formule;
    }

    public function setTitreFormule(string $titre_formule): self
    {
        $this->titre_formule = $titre_formule;

        return $this;
    }

    public function getDescriptionFormule(): ?string
    {
        return $this->description_formule;
    }

    public function setDescriptionFormule(string $description_formule): self
    {
        $this->description_formule = $description_formule;

        return $this;
    }

    public function getPrixFormule(): ?string
    {
        return $this->prix_formule;
    }

    public function setPrixFormule(string $prix_formule): self
    {
        $this->prix_formule = $prix_formule;

        return $this;
    }

    /**
     * @return Collection<int, Menu>
     */
    public function getMenus(): Collection
    {
        return $this->menus;
    }

    public function addMenu(Menu $menu): self
    {
        if (!$this->menus->contains($menu)) {
            $this->menus->add($menu);
            $menu->addFormule($this);
        }

        return $this;
    }

    public function removeMenu(Menu $menu): self
    {
        if ($this->menus->removeElement($menu)) {
            $menu->removeFormule($this);
        }

        return $this;
    }

    /*
     * Transforme cette entité en "Formule '<titre_formule>'"
     * Obligatoire pour traduire les champs "associations" d'EasyAdmin
    */
    public function __toString(): string
    {
        return 'Formule "'.$this->getTitreFormule().'"';
    }
}
