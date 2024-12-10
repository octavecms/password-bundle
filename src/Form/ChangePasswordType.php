<?php

namespace Octave\PasswordBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Octave\PasswordBundle\Repository\PasswordHistoryRepository;
use Octave\PasswordBundle\EventListener\PasswordHistorySubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChangePasswordType extends AbstractType
{
    private TranslatorInterface $translator;
    private UserPasswordHasherInterface $passwordHasher;
    private PasswordHistoryRepository $passwordHistoryRepository;
    private EntityManagerInterface $entityManager;
    private bool $askCurrentPassword;
    private string $userClass;
    private int $minLength;
    private int $maxLength;
    private string $complexityLevel;
    private bool $keepHistory;
    private int $historyCount;


    public function __construct(
        TranslatorInterface         $translator,
        UserPasswordHasherInterface $passwordHasher,
        PasswordHistoryRepository   $passwordHistoryRepository,
        EntityManagerInterface      $entityManager,
        bool                        $askCurrentPassword,
        string                      $userClass,
        int                         $minLength,
        int                         $maxLength,
        string                      $complexityLevel,
        bool                        $keepHistory,
        int                         $historyCount
    )
    {
        $this->translator = $translator;
        $this->passwordHasher = $passwordHasher;
        $this->passwordHistoryRepository = $passwordHistoryRepository;
        $this->entityManager = $entityManager;
        $this->askCurrentPassword = $askCurrentPassword;
        $this->userClass = $userClass;
        $this->minLength = $minLength;
        $this->maxLength = $maxLength;
        $this->complexityLevel = $complexityLevel;
        $this->keepHistory = $keepHistory;
        $this->historyCount = $historyCount;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $builder->getData();
        $isResetPassword = $options['is_reset_password'] ?? false;

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
            ])
        ];

        if ($this->askCurrentPassword) {
            $plainPasswordConstraints[] = new Callback([
                'callback' => function ($value, ExecutionContextInterface $context) use ($user) {
                    if ($this->passwordHasher->isPasswordValid($user, $value)) {
                        $context->buildViolation('Password is not changed')
                            ->atPath('plainPassword')
                            ->addViolation();
                    }
                }
            ]);
        }

        switch ($this->complexityLevel) {
            case 'high':
                $plainPasswordConstraints[] = new Regex([
                    'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#^()_+\-=\[\]{};:,.<>\/])/',
                    'message' => $this->translator->trans('octave_password.password.new.validation.complexity.high', [], 'octave_password')
                ]);
                break;
            case 'medium':
                $plainPasswordConstraints[] = new Regex([
                    'pattern' => '/(?=.*[a-zA-Z])(?=.*\d)/',
                    'message' => $this->translator->trans('octave_password.password.new.validation.complexity.medium', [], 'octave_password')
                ]);
                break;
        }

        if ($this->keepHistory) {
            $plainPasswordConstraints[] = new Callback([
                'callback' => function ($value, ExecutionContextInterface $context) use ($user) {
                    $passwordHistory = $this->passwordHistoryRepository->getPasswordHistory($user, $this->historyCount);
                    $sha256Hash = hash('sha256', $value);

                    foreach ($passwordHistory as $history) {
                        if ($sha256Hash === $history->getHashedPassword()) {
                            $context
                                ->buildViolation($this->translator->trans('octave_password.password.new.validation.previously_used', [], 'octave_password'))
                                ->atPath('plainPassword')
                                ->addViolation();
                            break;
                        }
                    }
                }
            ]);
        }

        $builder->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'required' => true,
            'first_options' => [
                'label' => 'octave_password.password.new.label',
                'constraints' => $plainPasswordConstraints
            ],
            'second_options' => [
                'label' => 'octave_password.password.new.confirmation.label'
            ],
            'invalid_message' => 'octave_password.password.new.validation.mismatch'
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