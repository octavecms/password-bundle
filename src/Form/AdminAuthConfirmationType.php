<?php

namespace Octave\PasswordBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class AdminAuthConfirmationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'octave_password.admin_auth.code.label',
                'required' => true,
                'attr' => [
                    'placeholder' => 'octave_password.admin_auth.code.placeholder',
                    'maxlength' => 6,
                    'autocomplete' => 'off',
                    'pattern' => '\d*',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'octave_password.admin_auth.code.required'
                    ]),
                    new Length([
                        'min' => 6,
                        'max' => 6,
                        'exactMessage' => 'octave_password.admin_auth.code.length'
                    ]),
                    new Regex([
                        'pattern' => '/^\d+$/',
                        'message' => 'octave_password.admin_auth.code.digits_only'
                    ])
                ]
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'octave_password',
        ]);
    }
}