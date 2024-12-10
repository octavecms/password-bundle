<?php

namespace Octave\PasswordBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Octave\PasswordBundle\Model\ResetUserInterface;

/**
 * @ORM\Entity(repositoryClass="Octave\PasswordBundle\Repository\PasswordHistoryRepository")
 * @ORM\Table(name="octave_password_history")
 */
class PasswordHistory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private ResetUserInterface $user;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private string $hashedPassword;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTimeInterface $createdAt;

    public function __construct(ResetUserInterface $user, string $hashedPassword)
    {
        $this->user = $user;
        $this->hashedPassword = $hashedPassword;
        $this->createdAt = new \DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): ResetUserInterface
    {
        return $this->user;
    }

    public function getHashedPassword(): string
    {
        return $this->hashedPassword;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
