<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\DBAL\Types\Types;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'This email is already in use.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(name: 'email', length: 200, type: Types::STRING, unique: true)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "L'adresse email n'est pas valide.")]
    private ?string $email = null;

    #[ORM\Column(name: 'roles', type: Types::JSON)]
    private array $roles = [];

    #[ORM\Column(name: 'password', length: 255, type: Types::STRING)]
    #[Assert\NotBlank(message: "Le mot de passe est obligatoire.")]
    #[Assert\Length(min: 6, minMessage: "Le mot de passe doit contenir au moins 6 caractères.")]
    private ?string $password = null;

    #[ORM\Column(name: 'postal_code', length: 10, type: Types::STRING)]
    #[Assert\NotBlank(message: "Le code postal est obligatoire.")]
    #[Assert\Regex(pattern: "/^\d{5}$/", message: "Le code postal doit contenir 5 chiffres.")]
    private ?string $postalCode = null;

    #[ORM\Column('created_at', type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column('update_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $updateAt = null;

    #[ORM\ManyToOne(targetEntity: City::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?City $city = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updateAt = new \DateTimeImmutable();
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

    public function getRoles(): array
    {
        return array_unique($this->roles);
    }
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }
    public function getUserIdentifier(): string
    {
        return $this->email;
    }
    public function eraseCredentials(): void {}

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeImmutable
    {
        return $this->updateAt;
    }

    public function setUpdateAt(\DateTimeImmutable $updateAt): static
    {
        $this->updateAt = $updateAt;

        return $this;
    }
    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): static
    {
        $this->city = $city;
        return $this;
    }
}
