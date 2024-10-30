<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MessageRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Message
{
    const ROLE_USER = "user";
    const ROLE_CHAT = "chat";

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type=Types::TEXT, nullable=true)
     */
    private ?string $text = null;

    /**
     * @ORM\Column(length=10)
     */
    private ?string $role = null;

    /**
     * @ORM\Column(type=Types::DATETIME_MUTABLE)
     */
    private ?\DateTimeInterface $logdate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getLogdate(): ?\DateTimeInterface
    {
        return $this->logdate;
    }

    public function setLogdate(\DateTimeInterface $logdate): self
    {
        $this->logdate = $logdate;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist(): void
    {
        $this->logdate = new \DateTime();
    }
}
