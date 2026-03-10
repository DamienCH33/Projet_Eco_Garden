<?php

namespace App\Entity;

use App\Repository\CityRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CityRepository::class)]
#[ORM\Table(name: 'city')]
#[ORM\UniqueConstraint(columns: ['name', 'postal_code', 'country'])]
class City
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 80)]
    #[Assert\NotBlank(message: 'Le nom de la ville est obligatoire.')]
    private string $name;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: 'Le code postal est obligatoire.')]
    #[Assert\Regex(
        pattern: "/^\d{5}$/",
        message: 'Le code postal doit contenir 5 chiffres.'
    )]
    private string $postalCode;

    #[ORM\Column(length: 40)]
    #[Assert\NotBlank(message: 'Le pays est obligatoire.')]
    private string $country;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = trim($name);

        return $this;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = strtoupper($country);

        return $this;
    }

    public function __toString(): string
    {
        return \sprintf('%s (%s)', $this->name, $this->postalCode);
    }
}
