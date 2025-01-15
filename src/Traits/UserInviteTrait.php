<?php

namespace Octave\PasswordBundle\Traits;

trait UserInviteTrait
{
    /**
     * @ORM\Column(type="boolean")
     */
    private bool $forcePasswordChange = false;

    public function isForcePasswordChange(): bool
    {
        return $this->forcePasswordChange;
    }

    public function setForcePasswordChange(bool $force): self
    {
        $this->forcePasswordChange = $force;
        return $this;
    }
}