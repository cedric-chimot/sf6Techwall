<?php

namespace App\Entity;

use App\Traits\TimeStampTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\HobbyRepository;

#[ORM\Entity(repositoryClass: HobbyRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class Hobby
{

    use TimeStampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 70)]
    private ?string $designation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    // public function __toString()
    // {
    //     return $this->designation;
    // }
    
}
