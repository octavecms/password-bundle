<?php

namespace Octave\PasswordBundle\Form;

use Octave\PasswordBundle\Validator\Constraints\PasswordComplexity;
use Octave\PasswordBundle\Validator\Constraints\UniquePassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Octave\PasswordBundle\EventListener\PasswordHistorySubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChangePasswordType extends AbstractType
{
    private TranslatorInterface $translator;
    private UserPasswordHasherInterface $passwordHasher;
    private EntityManagerInterface $entityManager;
    private bool $askCurrentPassword;
    private string $userClass;
    private int $minLength;
    private int $maxLength;
    private bool $keepHistory;
    private string $complexityLevel;

    public function __construct(
        TranslatorInterface         $translator,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface      $entityManager,
        bool                        $askCurrentPassword,
        string                      $userClass,
        int                         $minLength,
        int                         $maxLength,
        bool                        $keepHistory,
        string                      $complexityLevel,
    )
    {
        $this->translator = $translator;
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
        $this->askCurrentPassword = $askCurrentPassword;
        $this->userClass = $userClass;
        $this->minLength = $minLength;
        $this->maxLength = $maxLength;
        $this->keepHistory = $keepHistory;
        $this->complexityLevel = $complexityLevel;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $builder->getData();
        $isResetPassword = $options['is_reset_password'] ?? false;

        $complexity = $this->complexityLevel;

        $helpText = sprintf(
            'Password must be between %d and %d characters.',
            $this->minLength,
            $this->maxLength
        );

        if ($complexity && $complexity !== 'easy') {
            $complexityText = $this->translator->trans(
                "octave_password.password.new.validation.complexity.$complexity",
                [],
                'octave_password'
            );

            $helpText .= ' ' . $complexityText;
        }

        if (!$isResetPassword && $this->askCurrentPassword) {
            $builder
                ->add('currentPassword', PasswordType::class, [
                    'required' => true,
                    'label' => 'octave_password.password.current.label',
                    'mapped' => false,
                    'constraints' => [
                        new NotBlank(),
                        new Callback([
                            'callback' => function ($value, ExecutionContextInterface $context) use ($user) {
                                if (!$this->passwordHasher->isPasswordValid($user, $value)) {
                                    $context
                                        ->buildViolation($this->translator->trans('octave_password.password.new.validation.same_as_current', [], 'octave_password'))
                                        ->atPath('currentPassword')
                                        ->addViolation();
                                }
                            }
                        ]),
                    ]
                ]);
        }

        $plainPasswordConstraints = [
            new NotBlank(),
            new Length([
                'min' => $this->minLength,
                'minMessage' => $this->translator->trans('octave_password.password.new.validation.too_short', [], 'octave_password'),
                'max' => $this->maxLength,
                'maxMessage' => $this->translator->trans('octave_password.password.new.validation.too_long', [], 'octave_password'),
            ]),
            new UniquePassword(),
            new PasswordComplexity()
        ];

        $builder->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'required' => true,
            'first_options' => [
                'label' => 'octave_password.password.new.label',
                'constraints' => $plainPasswordConstraints
            ],
            'second_options' => [
                'label' => 'octave_password.password.new.confirmation.label',
                'help' => $helpText,
            ],
            'invalid_message' => $this->translator->trans('octave_password.password.new.validation.mismatch', [], 'octave_password')
        ]);

        $builder->addEventSubscriber(
            new PasswordHistorySubscriber($this->entityManager, $this->keepHistory)
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method' => 'POST',
            'data_class' => $this->userClass,
            'translation_domain' => 'octave_password',
            'is_reset_password' => false,
        ]);
    }

}