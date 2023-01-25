<?php

namespace App\Entity;

use App\Traits\TimeStampTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PersonneRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: PersonneRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class Personne
{

    use TimeStampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message:"Veuillez renseigner ce champs !")]
    #[Assert\Length(min:4, minMessage:"Ce champs doit comporter au moins 4 caractères !")]
    private ?string $firstname = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message:"Veuillez renseigner ce champs !")]
    #[Assert\Length(min:4, minMessage:"Ce champs doit comporter au moins 4 caractères !")]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $age = null;

    #[ORM\OneToOne(inversedBy: 'personne', cascade: ['persist', 'remove'])]
    private ?Profile $profile = null;

    #[ORM\ManyToMany(targetEntity: Hobby::class)]
    private Collection $hobbies;

    #[ORM\ManyToOne(inversedBy: 'personnes')]
    private ?Job $job = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'personnes')]
    private ?User $createdBy = null;

    public function __construct()
    {
        $this->hobbies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(?Profile $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * @return Collection<int, Hobby>
     */
    public function getHobbies(): Collection
    {
        return $this->hobbies;
    }

    public function addHobby(Hobby $hobby): self
    {
        if (!$this->hobbies->contains($hobby)) {
            $this->hobbies->add($hobby);
        }

        return $this;
    }

    public function removeHobby(Hobby $hobby): self
    {
        $this->hobbies->removeElement($hobby);

        return $this;
    }

    public function getJob(): ?Job
    {
        return $this->job;
    }

    public function setJob(?Job $job): self
    {
        $this->job = $job;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

}
