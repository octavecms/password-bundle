<?php

namespace Octave\PasswordBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Octave\PasswordBundle\Entity\UserInvite;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserInviteService
{
    private $em;

    private $mailer;

    private $router;

    const EXPIRATION_HOURS = 24;

    public function __construct(
        EntityManagerInterface $em,
        UrlGeneratorInterface $router,
    ) {
        $this->em = $em;
        $this->router = $router;
    }

    public function setMailer($mailer)
    {
        $this->mailer = $mailer;
    }

    public function createInvite($user): UserInvite
    {
        $invite = new UserInvite();
        $invite->setUser($user);
        $invite->setToken(bin2hex(random_bytes(32)));
        $invite->setExpiresAt(new \DateTime("+" . self::EXPIRATION_HOURS . " hours"));

        $this->em->persist($invite);
        $this->em->flush();

        $this->mailer->sendInviteEmail($invite);

        return $invite;
    }

    public function isValidInvite(string $token): bool
    {
        $invite = $this->em->getRepository(UserInvite::class)->findOneBy(['token' => $token]);

        return $invite
            && $invite->getExpiresAt() > new \DateTime()
            && $invite->getUsedAt() === null;
    }

    public function markInviteAsUsed(string $token): void
    {
        $invite = $this->em->getRepository(UserInvite::class)->findOneBy(['token' => $token]);

        if ($invite) {
            $invite->setUsedAt(new \DateTime());
            $this->em->flush();
        }
    }
}