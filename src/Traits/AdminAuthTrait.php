<?php

namespace Octave\PasswordBundle\Traits;

use Doctrine\ORM\Mapping as ORM;

trait AdminAuthTrait
{
    #[ORM\Column(type: 'string', length: 6, nullable: true)]
    private $adminAuthCode;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $adminAuthCodeCreatedAt;

    #[ORM\Column(type: 'boolean')]
    private $adminAuthConfirmed = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $lastAdminAuthConfirmation;

    public function generateAdminAuthCode(): void
    {
        $this->adminAuthCode = sprintf("%06d", random_int(0, 999999));
        $this->adminAuthCodeCreatedAt = new \DateTime();
        $this->adminAuthConfirmed = false;
    }

    public function getAdminAuthCode(): ?string
    {
        return $this->adminAuthCode;
    }

    public function getAdminAuthCodeCreatedAt(): ?\DateTime
    {
        return $this->adminAuthCodeCreatedAt;
    }

    public function isAdminAuthConfirmed(): bool
    {
        if (!$this->lastAdminAuthConfirmation) {
            return false;
        }

        $now = new \DateTime();
        $interval = $now->getTimestamp() - $this->lastAdminAuthConfirmation->getTimestamp();

        return $interval < 24 * 3600;
    }

    public function setAdminAuthConfirmed(bool $confirmed): void
    {
        $this->adminAuthConfirmed = $confirmed;
        if ($confirmed) {
            $this->lastAdminAuthConfirmation = new \DateTime();
        }
    }

    public function isAdminAuthCodeValid(int $codeLifetimeMinutes): bool
    {
        if (!$this->adminAuthCodeCreatedAt) {
            return false;
        }

        $expirationDate = (new \DateTime())->modify("-{$codeLifetimeMinutes} minutes");
        return $this->adminAuthCodeCreatedAt > $expirationDate;
    }

    public function clearAdminAuthCode(): void
    {
        $this->adminAuthCode = null;
        $this->adminAuthCodeCreatedAt = null;
    }

    public function setLastAdminAuthConfirmation(?\DateTime $dateTime): void
    {
        $this->lastAdminAuthConfirmation = $dateTime;
    }
}