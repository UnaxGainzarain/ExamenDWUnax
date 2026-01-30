<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Song
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $durationSeconds = null;

    #[ORM\ManyToOne(inversedBy: 'playList')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Activity $activity = null;

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }
    public function getDurationSeconds(): ?int { return $this->durationSeconds; }
    public function setDurationSeconds(int $durationSeconds): static { $this->durationSeconds = $durationSeconds; return $this; }
    public function getActivity(): ?Activity { return $this->activity; }
    public function setActivity(?Activity $activity): static { $this->activity = $activity; return $this; }
}