<?php

namespace Octave\PasswordBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

class PasswordComplexityValidator extends ConstraintValidator
{
    private TranslatorInterface $translator;
    private string $complexityLevel;

    public function __construct(TranslatorInterface $translator, string $complexityLevel)
    {
        $this->translator = $translator;
        $this->complexityLevel = $complexityLevel;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (null === $value) {
            return;
        }

        switch ($this->complexityLevel) {
            case 'high':
                $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#^()_+\-=\[\]{};:,.<>\/])/';
                if (!preg_match($pattern, $value)) {
                    $this->context->buildViolation($this->translator->trans('octave_password.password.new.validation.complexity.high', [], 'octave_password'))
                        ->addViolation();
                }
                break;

            case 'medium':
                $pattern = '/(?=.*[a-zA-Z])(?=.*\d)/';
                if (!preg_match($pattern, $value)) {
                    $this->context->buildViolation($this->translator->trans('octave_password.password.new.validation.complexity.medium', [], 'octave_password'))
                        ->addViolation();
                }
                break;
        }
    }
}