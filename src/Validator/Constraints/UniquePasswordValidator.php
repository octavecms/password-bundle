<?php

namespace Octave\PasswordBundle\Validator\Constraints;

use Octave\PasswordBundle\Repository\PasswordHistoryRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

class UniquePasswordValidator extends ConstraintValidator
{
    private $passwordHasher;
    private $passwordHistoryRepository;
    private $keepHistory;
    private $historyCount;
    private TranslatorInterface $translator;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        PasswordHistoryRepository   $passwordHistoryRepository,
        bool                        $keepHistory,
        int                         $historyCount,
        TranslatorInterface         $translator
    )
    {
        $this->passwordHasher = $passwordHasher;
        $this->passwordHistoryRepository = $passwordHistoryRepository;
        $this->keepHistory = $keepHistory;
        $this->historyCount = $historyCount;
        $this->translator = $translator;
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (null === $value) return;

        $user = $this->context->getRoot()->getData();
        if ($this->passwordHasher->isPasswordValid($user, $value)) {
            $this->context->buildViolation($this->translator->trans('octave_password.password.new.validation.current', [], 'octave_password'))
                ->atPath('plainPassword')
                ->addViolation();
        }

        if ($this->keepHistory) {
            $passwordHistory = $this->passwordHistoryRepository->getPasswordHistory($user, $this->historyCount);
            $sha256Hash = hash('sha256', $value);

            foreach ($passwordHistory as $history) {
                if ($sha256Hash === $history->getHashedPassword()) {
                    $this->context->buildViolation($this->translator->trans('octave_password.password.new.validation.previously_used', [], 'octave_password'))
                        ->atPath('plainPassword')
                        ->addViolation();
                    break;
                }
            }
        }
    }
}