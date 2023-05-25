<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\RaseResultRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RaseResultRepository::class)]
#[ApiResource]
class RaseResult
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 255)]
    public ?string $full_name = null;

    #[ORM\Column(length: 255)]
    public ?string $distance = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    public ?\DateTimeInterface $time = null;

    #[ORM\Column(length: 255)]
    public ?string $age_category = null;

    #[ORM\Column(nullable: true)]
    public ?int $overall_placement = null;

    #[ORM\Column(nullable: true)]
    public ?int $age_category_placement = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    public ?\DateTimeInterface $created_at = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->full_name;
    }

    public function setFullName(string $full_name): self
    {
        $this->full_name = $full_name;

        return $this;
    }

    public function getDistance(): ?string
    {
        return $this->distance;
    }

    public function setDistance(string $distance): self
    {
        $this->distance = $distance;

        return $this;
    }

    public function getTime(): ?\DateTimeInterface
    {
        return $this->time;
    }

    public function setTime(\DateTimeInterface $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getAgeCategory(): ?string
    {
        return $this->age_category;
    }

    public function setAgeCategory(string $age_category): self
    {
        $this->age_category = $age_category;

        return $this;
    }

    public function getOverallPlacement(): ?int
    {
        return $this->overall_placement;
    }

    public function setOverallPlacement(?int $overall_placement): self
    {
        $this->overall_placement = $overall_placement;

        return $this;
    }

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
}