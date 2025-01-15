<?php

namespace Octave\PasswordBundle\Model;

interface UserInviteInterface
{
    public function isForcePasswordChange(): bool;
    public function setForcePasswordChange(bool $force): self;
}