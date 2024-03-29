<?php

namespace Octave\PasswordBundle\Traits;

trait UserPasswordExpiredTrait
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $passwordChangedAt;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
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
    public function isPasswordExpired($lifetime)
    {
        if (! $this->getPasswordChangedAt()) {
            return false;
        }

        $currentDate = new \DateTime();
        $expirationDate = $this->getPasswordChangedAt()->modify("+$lifetime day");

        return $currentDate >= $expirationDate;
    }
}