<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Put;
use App\Repository\RaceResultRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RaceResultRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['read']]),
        new GetCollection(normalizationContext: ['groups' => ['read']]),
        new Put(
            normalizationContext: ['groups' => ['write']],
            denormalizationContext: ['groups' => ['write']]
        )
    ],
    formats: ['json' => 'application/json'],

)]
#[ApiFilter(SearchFilter::class, properties: ['full_name' => 'partial', 'distance' => 'partial', 'age_category' => 'partial'])]
#[ApiFilter(OrderFilter::class, properties: ['id', 'full_name', 'distance', 'time', 'age_category', 'overall_placement'])]
class RaceResult
{
    //race
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Races::class)]
    #[ORM\JoinColumn(name: "race_id", referencedColumnName: "id")]
    private $race;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The fullName field cannot be null.")]
    public ?string $full_name = null;

    #[ORM\Column(length: 255)]
    #[Assert\Choice(choices: ['medium', 'long'], message: "The value '{{ value }}' is not a valid choice for the distance field. Allowed values are 'medium' and 'long'."
    )]
    #[Assert\NotBlank(message: "The distance field cannot be null.")]
    public ?string $distance = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Assert\NotBlank(message: "The time field cannot be null.")]
    public ?\DateTimeInterface $time = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The ageCategory field cannot be null.")]
    public ?string $age_category = null;

    #[ORM\Column(nullable: true)]
    public ?int $overall_placement = null;

    #[ORM\Column(nullable: true)]
    public ?int $age_category_placement = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    public ?\DateTimeInterface $created_at = null;

    #[Groups(['read'])]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[Groups(['read', 'write'])]
    public function getFullName(): ?string
    {
        return $this->full_name;
    }

    public function setFullName(string $full_name): self
    {
        $this->full_name = $full_name;

        return $this;
    }

    #[Groups(['read', 'write'])]
    public function getDistance(): ?string
    {
        return $this->distance;
    }

    public function setDistance(string $distance): self
    {
        $this->distance = $distance;

        return $this;
    }

    #[Groups(['read', 'write'])]
    public function getTime(): ?string
    {
        // return $this->time;
        return $this->time !== null ? $this->time->format('H:i:s') : null;

    }

    public function setTime(\DateTimeInterface $time): self
    {
        $this->time = $time;

        return $this;
    }

    #[Groups(['read', 'write'])]
    public function getAgeCategory(): ?string
    {
        return $this->age_category;
    }

    public function setAgeCategory(string $age_category): self
    {
        $this->age_category = $age_category;

        return $this;
    }

    #[Groups(['read'])]
    public function getOverallPlacement(): ?int
    {
        return $this->overall_placement;
    }

    public function setOverallPlacement(?int $overall_placement): self
    {
        $this->overall_placement = $overall_placement;

        return $this;
    }

    #[Groups(['read'])]
    public function getAgeCategoryPlacement(): ?int
    {
        return $this->age_category_placement;
    }

    public function setAgeCategoryPlacement(?int $age_category_placement): self
    {
        $this->age_category_placement = $age_category_placement;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }
    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }
    #[Groups(['read'])]

    public function getRace(): ?Races
    {
        return $this->race;
    }

    public function setRace(?Races $race): self
    {
        $this->race = $race;

        return $this;
    }

}