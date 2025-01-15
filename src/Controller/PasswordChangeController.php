<?php

namespace Octave\PasswordBundle\Controller;

use FOS\UserBundle\Model\UserManagerInterface;
use Octave\PasswordBundle\Form\ChangePasswordType;
use Octave\PasswordBundle\Form\ResetPasswordRequestType;
use Octave\PasswordBundle\Model\ResetUserInterface;
use Octave\PasswordBundle\Model\UserInviteInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Octave\PasswordBundle\Model\ResetMailerInterface;

class PasswordChangeController extends AbstractController
{
    private ResetMailerInterface $mailer;
    private UserManagerInterface $userManager;
    private TranslatorInterface $translator;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        ResetMailerInterface        $mailer,
        UserManagerInterface        $userManager,
        TranslatorInterface         $translator,
        UserPasswordHasherInterface $passwordHasher
    )
    {
        $this->mailer = $mailer;
        $this->userManager = $userManager;
        $this->translator = $translator;
        $this->passwordHasher = $passwordHasher;
    }

    public function changePassword(Request $request): Response
    {
        /** @var ResetUserInterface $user */
        $user = $this->getUser();

        $isForceChange = false;
        if ($user instanceof UserInviteInterface) {
            $isForceChange = $user->isForcePasswordChange();
        }

        $form = $this->createForm(ChangePasswordType::class, $user, [
            'is_reset_password' => $isForceChange
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPasswordChangedAt(new \DateTime());

            if ($isForceChange) {
                $user->setForcePasswordChange(false);
            }
            $this->userManager->updateUser($user);

            return $this->redirectToRoute('sonata_admin_dashboard');
        }

        return $this->render('@OctavePassword/change_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function renewPassword($token, Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('sonata_admin_dashboard');
        }

        if (!$token) {
            throw new NotFoundHttpException();
        }

        /** @var ResetUserInterface $user */
        $user = $this->userManager->findUserBy(['passwordChangeToken' => $token]);
        if (!$user) {
            throw new NotFoundHttpException();
        }

        $tokenLifetime = $this->getParameter('octave.password.reset.token.lifetime');
        $now = new \DateTime();
        $requestedAt = $user->getPasswordRequestedAt();

        if (!$requestedAt || $now->getTimestamp() - $requestedAt->getTimestamp() > $tokenLifetime * 60) {
            $user->setPasswordChangeToken(null);
            $this->userManager->updateUser($user);

            $this->addFlash('error', $this->translator->trans('octave_password.reset.token_expired', [], 'octave_password'));
            return $this->redirectToRoute('fos_user_security_login');
        }

        $form = $this->createForm(ChangePasswordType::class, $user, [
            'is_reset_password' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setPasswordChangeToken(null);
            $user->setPassword(
                $this->passwordHasher->hashPassword(
                    $user,
                    $plainPassword
                )
            );
            $user->setPasswordChangedAt(new \DateTime());
            $this->userManager->updateUser($user);

            return $this->redirectToRoute('sonata_admin_dashboard');
        }

        return $this->render('@OctavePassword/renew_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function request(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('sonata_admin_dashboard');
        }

        $form = $this->createForm(ResetPasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $this->userManager->findUserByEmail($email);

            if ($user) {
                $now = new \DateTime();
                $resendIntervalMinutes = $this->getParameter('octave.password.reset.resend.interval');

                $lastSentAt = $user->getPasswordRequestedAt();

                if ($lastSentAt && $now->getTimestamp() - $lastSentAt->getTimestamp() < $resendIntervalMinutes * 60) {
                    $this->addFlash('error', $this->translator->trans('octave_password.reset.interval_error', [
                        '%minutes%' => $resendIntervalMinutes
                    ], 'octave_password'));
                    return $this->redirectToRoute('fos_user_security_login');
                }

                $token = $user->generateToken();
                $user->setPasswordChangeToken($token);
                $user->setPasswordRequestedAt(new \DateTime());

                $this->userManager->updateUser($user);
                $this->mailer->sendReset($user);

                $this->addFlash('success', $this->translator->trans('octave_password.reset.email_sent', [], 'octave_password'));
                return $this->redirectToRoute('fos_user_security_login');
            }

            $this->addFlash('error', $this->translator->trans('octave_password.email.not_found', [], 'octave_password'));
        }

        return $this->render('@OctavePassword/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}