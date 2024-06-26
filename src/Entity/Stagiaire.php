<?php

namespace App\Entity;

use App\Repository\StagiaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StagiaireRepository::class)]
class Stagiaire
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 50)]
  private ?string $nom = null;

  #[ORM\Column(length: 50)]
  private ?string $prenom = null;

  #[ORM\Column(length: 10, nullable: true)]
  private ?string $sexe = null;

  #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
  private ?\DateTimeInterface $date_naissance = null;

  #[ORM\Column(length: 50)]
  private ?string $ville = null;

  #[ORM\Column(length: 50)]
  private ?string $email = null;

  #[ORM\Column(length: 20)]
  private ?string $tel = null;

  /**
   * @var Collection<int, Session>
   */
  #[ORM\ManyToMany(targetEntity: Session::class, mappedBy: 'stagiaires')]
  private Collection $sessions;

  public function __construct()
  {
    $this->sessions = new ArrayCollection();
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getNom(): ?string
  {
    return $this->nom;
  }

  public function setNom(string $nom): static
  {
    $this->nom = $nom;

    return $this;
  }

  public function getPrenom(): ?string
  {
    return $this->prenom;
  }

  public function setPrenom(string $prenom): static
  {
    $this->prenom = $prenom;

    return $this;
  }

  public function getSexe(): ?string
  {
    return $this->sexe;
  }

  public function setSexe(?string $sexe): static
  {
    $this->sexe = $sexe;

    return $this;
  }

  public function getDateNaissance(): ?\DateTimeInterface
  {
    return $this->date_naissance;
  }

  public function setDateNaissance(?\DateTimeInterface $date_naissance): static
  {
    $this->date_naissance = $date_naissance;

    return $this;
  }

  public function getVille(): ?string
  {
    return $this->ville;
  }

  public function setVille(string $ville): static
  {
    $this->ville = $ville;

    return $this;
  }

  public function getEmail(): ?string
  {
    return $this->email;
  }

  public function setEmail(string $email): static
  {
    $this->email = $email;

    return $this;
  }

  public function getTel(): ?string
  {
    return $this->tel;
  }

  public function setTel(string $tel): static
  {
    $this->tel = $tel;

    return $this;
  }

  /**
   * @return Collection<int, Session>
   */
  public function getSessions(): Collection
  {
    return $this->sessions;
  }

  public function addSession(Session $session): static
  {
    if (!$this->sessions->contains($session)) {
      $this->sessions->add($session);
      $session->addStagiaire($this);
    }

    return $this;
  }

  public function removeSession(Session $session): static
  {
    if ($this->sessions->removeElement($session)) {
      $session->removeStagiaire($this);
    }

    return $this;
  }

  public function __toString(): string
  {
    return $this->prenom . " " . $this->nom;
  }
}
