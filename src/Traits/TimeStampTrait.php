<?php

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;

trait TimeStampTrait
{
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $updatedAt = null;

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersist() {
        if (!$this->getCreatedAt()){
            $this->createdAt = new \DateTimeImmutable();
        }

        if (!$this->getupdatedAt()){
            $this->updatedAt = new \DateTime();
        }
    }

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdate() {
        $this->updatedAt = new \DateTime();
    }

}