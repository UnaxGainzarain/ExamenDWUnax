<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null; // 'BodyPump', 'Spinning', 'Core'

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateStart = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateEnd = null;

    #[ORM\Column]
    private ?int $maxParticipants = null;

    // Relación 1-M con Songs (Playlist)
    #[ORM\OneToMany(mappedBy: 'activity', targetEntity: Song::class, cascade: ['persist'])]
    private Collection $playList;

    // Relación para saber los apuntados
    #[ORM\OneToMany(mappedBy: 'activity', targetEntity: Booking::class)]
    private Collection $bookings;

    public function __construct()
    {
        $this->playList = new ArrayCollection();
        $this->bookings = new ArrayCollection();
    }

    // Getters y Setters...
    public function getId(): ?int { return $this->id; }
    public function getType(): ?string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }
    public function getDateStart(): ?\DateTimeInterface { return $this->dateStart; }
    public function setDateStart(\DateTimeInterface $dateStart): static { $this->dateStart = $dateStart; return $this; }
    public function getDateEnd(): ?\DateTimeInterface { return $this->dateEnd; }
    public function setDateEnd(\DateTimeInterface $dateEnd): static { $this->dateEnd = $dateEnd; return $this; }
    public function getMaxParticipants(): ?int { return $this->maxParticipants; }
    public function setMaxParticipants(int $maxParticipants): static { $this->maxParticipants = $maxParticipants; return $this; }
    
    public function getPlayList(): Collection { return $this->playList; }
    
    public function getBookings(): Collection { return $this->bookings; }
}