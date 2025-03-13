<?php

namespace Octave\PasswordBundle\EventListener;

use Octave\PasswordBundle\Model\UserInviteInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ForcePasswordChangeListener implements EventSubscriberInterface
{
    private $router;
    private $tokenStorage;

    public function __construct(
        UrlGeneratorInterface $router,
        TokenStorageInterface $tokenStorage,
    )
    {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token) return;

        $user = $token->getUser();

        if (!$user instanceof UserInviteInterface) return;

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        $allowedRoutes = [
            'octave.password.change.password',
            'app_logout'
        ];

        if ($user->isForcePasswordChange() && !in_array($route, $allowedRoutes)) {
            $event->setResponse(new RedirectResponse(
                $this->router->generate('octave.password.change.password')
            ));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}