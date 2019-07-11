<?php

namespace Octave\PasswordBundle\Model;

interface ResetMailerInterface
{
    public function sendReset(ResetUserInterface $user);
}