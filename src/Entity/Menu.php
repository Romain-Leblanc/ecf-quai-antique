<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $titre_menu = null;

    #[ORM\ManyToMany(targetEntity: Formule::class, inversedBy: 'menus')]
    #[ORM\OrderBy(['titre_formule' => 'ASC'])]
    private Collection $formules;

    public function __construct()
    {
        $this->formules = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitreMenu(): ?string
    {
        return $this->titre_menu;
    }

    public function setTitreMenu(string $titre_menu): self
    {
        $this->titre_menu = $titre_menu;

        return $this;
    }

    /**
     * @return Collection<int, Formule>
     */
    public function getFormules(): Collection
    {
        return $this->formules;
    }

    public function addFormule(Formule $formule): self
    {
        if (!$this->formules->contains($formule)) {
            $this->formules->add($formule);
        }

        return $this;
    }

    public function removeFormule(Formule $formule): self
    {
        $this->formules->removeElement($formule);

        return $this;
    }
}
