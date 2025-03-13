<?php

namespace Octave\PasswordBundle\Model;

interface AdminAuthMailerInterface
{
    public function sendCodeConfirmation(AdminAuthUserInterface $user);
}