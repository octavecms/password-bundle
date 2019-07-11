<?php

namespace Octave\PasswordBundle\Form;

use Rollerworks\Component\PasswordStrength\Validator\Constraints\PasswordRequirements;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ChangePasswordType extends AbstractType
{
    /** @var UserPasswordEncoderInterface  */
    private $passwordEncoder;

    /** @var bool */
    private $askCurrentPassword;

    /** @var integer */
    private $passwordMinLength;

    /** @var integer */
    private $passwordMaxLength;

    /** @var string */
    private $userClass;

    /** @var array */
    private $passwordRequirements = [];

    /**
     * PasswordChangeType constructor.
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param $useCurrentPassword
     * @param $passwordMinLength
     * @param $passwordMaxLength
     * @param $userClass
     * @param $passwordRequirements
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder, $useCurrentPassword, $passwordMinLength,
                                $passwordMaxLength, $userClass, $passwordRequirements)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->askCurrentPassword = $useCurrentPassword;
        $this->passwordMinLength = $passwordMinLength;
        $this->passwordMaxLength = $passwordMaxLength;
        $this->userClass = $userClass;
        $this->passwordRequirements = $passwordRequirements;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $builder->getData();

        if ($this->askCurrentPassword) {

            $builder
                ->add('currentPassword', PasswordType::class, [
                    'required' => true,
                    'mapped' => false,
                    'constraints' => [
                        new Callback([
                            'callback' => function($value, ExecutionContextInterface $context) use ($user){
                                if (! $this->passwordEncoder->isPasswordValid($user, $value)) {
                                    $context
                                        ->buildViolation('Wrong password')
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
            new Length(['min' => $this->passwordMinLength, 'max' => $this->passwordMaxLength]),
        ];

        if ($this->askCurrentPassword) {
            $plainPasswordConstraints[] = new Callback([
                'callback' => function($value, ExecutionContextInterface $context) use ($user){
                    if ($this->passwordEncoder->isPasswordValid($user, $value)) {
                        $context->buildViolation('Password is not changed')
                            ->atPath('plainPassword')
                            ->addViolation();
                    }
                }
            ]);
        }

        if ($this->passwordRequirements) {
            $plainPasswordConstraints[] = new PasswordRequirements($this->passwordRequirements);
        }

        $builder
            ->add('plainPassword', RepeatedType::class, [
                'required' => true,
                'type' => PasswordType::class,
                'constraints' => $this->passwordRequirements
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method' => 'POST',
            'data_class' => $this->userClass
        ]);
    }

}