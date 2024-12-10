<?php

namespace Octave\PasswordBundle\Traits;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Octave\PasswordBundle\Entity\PasswordHistory;

trait UserPasswordHistoryTrait
{
    /**
     * @ORM\OneToMany(targetEntity="Octave\PasswordBundle\Entity\PasswordHistory", mappedBy="user", cascade={"persist", "remove"})
     */
    private $passwordHistories;

    public function __construct()
    {
        $this->passwordHistories = new ArrayCollection();
    }

    /**
     * @return Collection|PasswordHistory[]
     */
    public function getPasswordHistories(): Collection
    {
        return $this->passwordHistories;
    }

    public function addPasswordHistory(PasswordHistory $passwordHistory): self
    {
        if (!$this->passwordHistories->contains($passwordHistory)) {
            $this->passwordHistories[] = $passwordHistory;
            $passwordHistory->setUser($this);
        }

        return $this;
    }

    public function removePasswordHistory(PasswordHistory $passwordHistory): self
    {
        if ($this->passwordHistories->removeElement($passwordHistory)) {
            if ($passwordHistory->getUser() === $this) {
                $passwordHistory->setUser(null);
            }
        }

        return $this;
    }
}