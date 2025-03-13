<?php

namespace Octave\PasswordBundle\EventListener;

use FOS\UserBundle\Model\UserManagerInterface;
use Octave\PasswordBundle\Model\AdminAuthUserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class AdminAuthSubscriber implements EventSubscriberInterface
{
    private const ALLOWED_ROUTES = [
        'octave.password.auth.confirmation',
        'octave.password.auth.resend',
        'fos_user_security_logout'
    ];

    private $router;
    private $tokenStorage;
    private $requireConfirmation;
    private $codeLifetime;
    private UserManagerInterface $userManager;
    private $mailer;
    private $adminPrefix;

    public function __construct(
        UrlGeneratorInterface $router,
        TokenStorageInterface $tokenStorage,
        bool                  $requireConfirmation,
        int                   $codeLifetime,
        UserManagerInterface  $userManager,
        string                $adminPrefix
    )
    {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->requireConfirmation = $requireConfirmation;
        $this->codeLifetime = $codeLifetime;
        $this->userManager = $userManager;
        $this->adminPrefix = $adminPrefix;
    }

    public function setMailer($mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin'
        ];
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof AdminAuthUserInterface) {
            $user->setLastAdminAuthConfirmation(null);
            $user->generateAdminAuthCode();
            $this->userManager->updateUser($user);
            $this->mailer->sendCodeConfirmation($user);
        }
    }

    public function onKernelRequest(RequestEvent $event): void
    {

        if (!$this->requireConfirmation) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token) return;

        $user = $token->getUser();
        if (!$user instanceof AdminAuthUserInterface) return;

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        if (!str_starts_with($path, $this->adminPrefix)) {
            return;
        }

        $routeName = $request->get('_route');
        $confirmationUrl = $this->router->generate('octave.password.auth.confirmation');

        if (in_array($routeName, self::ALLOWED_ROUTES, true) ||
            $path === parse_url($confirmationUrl, PHP_URL_PATH)) {
            return;
        }

        if ($user->isAdminAuthConfirmed()) {
            return;
        }

        if (!$user->getAdminAuthCode() || !$user->isAdminAuthCodeValid($this->codeLifetime)) {
            $user->generateAdminAuthCode();
            $this->userManager->updateUser($user);
            $this->mailer->sendCodeConfirmation($user);
        }

        $event->setResponse(new RedirectResponse($confirmationUrl));
    }
}