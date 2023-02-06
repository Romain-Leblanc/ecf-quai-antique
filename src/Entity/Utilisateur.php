<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Il existe déjà un compte avec cette adresse mail.')]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = ['ROLE_USER'];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    private ?string $prenom = null;

    #[ORM\Column]
    private ?int $nombre_convives = null;

    #[ORM\OneToMany(mappedBy: 'fk_utilisateur', targetEntity: Reservation::class)]
    private Collection $reservations;

    #[ORM\OneToMany(mappedBy: 'fk_utilisateur', targetEntity: AllergieUtilisateur::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $allergieUtilisateurs;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
        $this->allergieUtilisateurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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
            $reservation->setFkUtilisateur($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getFkUtilisateur() === $this) {
                $reservation->setFkUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AllergieUtilisateur>
     */
    public function getAllergieUtilisateurs(): Collection
    {
        return $this->allergieUtilisateurs;
    }

    public function addAllergieUtilisateur(AllergieUtilisateur $allergieUtilisateur): self
    {
        if (!$this->allergieUtilisateurs->contains($allergieUtilisateur)) {
            $this->allergieUtilisateurs->add($allergieUtilisateur);
            $allergieUtilisateur->setFkUtilisateur($this);
        }

        return $this;
    }

    public function removeAllergieUtilisateur(AllergieUtilisateur $allergieUtilisateur): self
    {
        if ($this->allergieUtilisateurs->removeElement($allergieUtilisateur)) {
            // set the owning side to null (unless already changed)
            if ($allergieUtilisateur->getFkUtilisateur() === $this) {
                $allergieUtilisateur->setFkUtilisateur(null);
            }
        }

        return $this;
    }

    /* Retourne le nom/prénom de l'utilisateur */
    public function getNomComplet(): ?string
    {
        return mb_strtoupper($this->getNom())." ".ucfirst($this->getPrenom());
    }
}
