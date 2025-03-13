<?php

namespace Octave\PasswordBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Octave\PasswordBundle\Entity\UserInvite;
use Octave\PasswordBundle\Form\AdminAuthConfirmationType;
use Octave\PasswordBundle\Model\AdminAuthMailerInterface;
use Octave\PasswordBundle\Model\AdminAuthUserInterface;
use Octave\PasswordBundle\Service\UserInviteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminAuthController extends AbstractController
{
    private AdminAuthMailerInterface $mailer;
    private UserManagerInterface $userManager;
    private TranslatorInterface $translator;

    public function __construct(
        AdminAuthMailerInterface $mailer,
        UserManagerInterface     $userManager,
        TranslatorInterface      $translator
    )
    {
        $this->mailer = $mailer;
        $this->userManager = $userManager;
        $this->translator = $translator;
    }

    public function confirmation(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof AdminAuthUserInterface) {
            throw $this->createAccessDeniedException();
        }

        if ($user->isAdminAuthConfirmed() || !$this->getParameter('octave.admin_auth.require.confirmation')) {
            return $this->redirectToRoute('sonata_admin_dashboard');
        }

        $form = $this->createForm(AdminAuthConfirmationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $code = $form->get('code')->getData();

            $codeLifetime = $this->getParameter('octave.admin_auth.confirmation.code.lifetime');

            if ($user->isAdminAuthCodeValid($codeLifetime)) {
                if ($user->getAdminAuthCode() === $code) {
                    $user->setAdminAuthConfirmed(true);
                    $user->clearAdminAuthCode();
                    $this->userManager->updateUser($user);

                    return $this->redirectToRoute('sonata_admin_dashboard');
                } else {
                    $this->addFlash('error', $this->translator->trans('octave_password.admin_auth.code.invalid', [], 'octave_password'));
                }
            } else {
                $user->clearAdminAuthCode();
                $this->userManager->updateUser($user);
                $this->addFlash('error', $this->translator->trans('octave_password.admin_auth.code.expired', [], 'octave_password'));
            }
        }

        $linkResend = $this->generateUrl('octave.password.auth.resend');
        return $this->render('@OctavePassword/admin_auth/confirmation.html.twig', [
            'form' => $form->createView(),
            'linkResend' => $linkResend
        ]);
    }

    public function resendCode(Request $request): Response
    {
        $user = $this->getUser();
        $codeLifetime = $this->getParameter('octave.admin_auth.confirmation.code.lifetime');

        if (!$user instanceof AdminAuthUserInterface) {
            throw $this->createAccessDeniedException();
        }

        if ($user->isAdminAuthConfirmed() || !$this->getParameter('octave.admin_auth.require.confirmation')) {
            return $this->redirectToRoute('sonata_admin_dashboard');
        }

        if ($user->isAdminAuthCodeValid($codeLifetime)) {
            $this->addFlash('error', $this->translator->trans('octave_password.admin_auth.resend_code.interval_error', [
                '%minutes%' => $codeLifetime
            ], 'octave_password'));
        } else {
            $user->generateAdminAuthCode();
            $this->userManager->updateUser($user);

            try {
                $this->mailer->sendCodeConfirmation($user);
                $this->addFlash('success', $this->translator->trans('octave_password.admin_auth.resend_code.success', [], 'octave_password'));
            } catch (\Exception $e) {
                $this->addFlash('error', $this->translator->trans('octave_password.admin_auth.resend_code.error', [], 'octave_password'));
            }
        }

        return $this->redirectToRoute('octave.password.auth.confirmation');
    }

    public function invite(Request $request, string $token, EntityManagerInterface $entityManager,
                           UserInviteService $inviteService, TokenStorageInterface $storage, EventDispatcherInterface $dispatcher): Response
    {
        if (!$inviteService->isValidInvite($token)) {
            throw $this->createNotFoundException('Invalid or expired invite link');
        }

        $invite = $entityManager->getRepository(UserInvite::class)->findOneBy(['token' => $token]);
        $user = $invite->getUser();

        $user->setForcePasswordChange(true);
        $entityManager->flush();

        $userToken = new UsernamePasswordToken(
            $user,
            'main',
            $user->getRoles()
        );

        $storage->setToken($userToken);

        $event = new InteractiveLoginEvent($request, $userToken);
        $dispatcher->dispatch($event);

        $inviteService->markInviteAsUsed($token);

        return $this->redirectToRoute('octave.password.change.password');
    }
}