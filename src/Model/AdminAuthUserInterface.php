<?php

namespace Octave\PasswordBundle\Model;

use FOS\UserBundle\Model\UserInterface;

interface AdminAuthUserInterface extends UserInterface
{
    public function generateAdminAuthCode(): void;

    public function getAdminAuthCode(): ?string;

    public function getAdminAuthCodeCreatedAt(): ?\DateTime;

    public function isAdminAuthConfirmed(): bool;

    public function setAdminAuthConfirmed(bool $confirmed): void;

    public function isAdminAuthCodeValid(int $codeLifetimeMinutes): bool;

    public function clearAdminAuthCode(): void;

    public function setLastAdminAuthConfirmation(?\DateTime $dateTime): void;
}