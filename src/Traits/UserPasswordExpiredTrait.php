<?php

namespace Octave\PasswordBundle\Traits;

use Doctrine\ORM\Mapping as ORM;

trait UserPasswordExpiredTrait
{
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $passwordChangedAt;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $passwordChangeToken;

    /**
     * @return \DateTime
     */
    public function getPasswordChangedAt()
    {
        return $this->passwordChangedAt;
    }

    /**
     * @param \DateTime $passwordChangedAt
     */
    public function setPasswordChangedAt($passwordChangedAt)
    {
        $this->passwordChangedAt = $passwordChangedAt;
    }

    /**
     * @return string
     */
    public function getPasswordChangeToken()
    {
        return $this->passwordChangeToken;
    }

    /**
     * @param string $passwordChangeToken
     */
    public function setPasswordChangeToken($passwordChangeToken)
    {
        $this->passwordChangeToken = $passwordChangeToken;
    }

    /**
     * @param $lifetime
     * @return bool
     * $lifetime in days
     * @throws \Exception
     */
    public function isPasswordExpired($lifetime): bool
    {
        if (!$this->getPasswordChangedAt() || $lifetime === 0) {
            return false;
        }

        $now = new \DateTime();
        $expirationDate = (clone $this->getPasswordChangedAt())->modify(sprintf('+%d days', $lifetime));

        return $now > $expirationDate;
    }

    public function generateToken(): string
    {
        return sha1(time() . uniqid());
    }
}