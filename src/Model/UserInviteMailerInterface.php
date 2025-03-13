<?php

namespace Octave\PasswordBundle\Model;

use Octave\PasswordBundle\Entity\UserInvite;

interface UserInviteMailerInterface
{
    public function sendInviteEmail(UserInvite $invite): int;
}