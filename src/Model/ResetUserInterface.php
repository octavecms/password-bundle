<?php

namespace Octave\PasswordBundle\Model;

use FOS\UserBundle\Model\UserInterface;

interface ResetUserInterface extends UserInterface
{
    public function getPasswordChangeToken();

    public function setPasswordChangeToken($token);

    public function setPasswordChangedAt($datetime);
}