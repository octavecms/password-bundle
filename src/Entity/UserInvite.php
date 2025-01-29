<?php

namespace Octave\PasswordBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * * @ORM\Table(name="user_invites")
 */
class UserInvite
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $token;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTime $expiresAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $usedAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return self
     */
    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getExpiresAt(): DateTime
    {
        return $this->expiresAt;
    }

    /**
     * @param mixed $expiresAt
     * @return self
     */
    public function setExpiresAt(DateTime $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function getUsedAt(): ?DateTime
    {
        return $this->usedAt;
    }

    public function setUsedAt(?DateTime $usedAt): self
    {
        $this->usedAt = $usedAt;
        return $this;
    }
}
