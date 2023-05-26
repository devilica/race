<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use App\Repository\RacesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;


#[ORM\Entity(repositoryClass: RacesRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection()
    ],
    formats: ['json' => 'application/json'],
    normalizationContext: ['groups' => ['read']]
)]
#[ApiFilter(SearchFilter::class, properties: ['title' => 'partial'])]
#[ApiFilter(OrderFilter::class, properties: ['id', 'title', 'average_long_time', 'average_medium_time'])]

class Races
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[ApiProperty(writable: true, readable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $average_long_time = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $average_medium_time = null;

    #[ORM\OneToMany(targetEntity: RaceResult::class, mappedBy: "race")]
    private $raceResults;
    #[Groups(['read'])]

    public function getId(): ?int
    {
        return $this->id;
    }
    #[Groups(['read'])]
    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
    #[Groups(['read'])]
    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }
    #[Groups(['read'])]
    public function getAverageLongTime(): ?string
    {
        //  return $this->average_long_time;
        return $this->average_long_time !== null ? $this->average_long_time->format('H:i:s') : null;

    }

    public function setAverageLongTime(\DateTimeInterface $average_long_time): self
    {
        $this->average_long_time = $average_long_time;

        return $this;
    }
    #[Groups(['read'])]
    public function getAverageMediumTime(): ?string
    {
        // return $this->average_medium_time;
        return $this->average_medium_time !== null ? $this->average_medium_time->format('H:i:s') : null;

    }

    public function setAverageMediumTime(\DateTimeInterface $average_medium_time): self
    {
        $this->average_medium_time = $average_medium_time;

        return $this;
    }

    public function getRaceResults(): Collection
    {
        return $this->raceResults;
    }
}