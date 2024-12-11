<?php

namespace Octave\PasswordBundle\EventListener;

use FOS\UserBundle\Model\UserInterface;
use Octave\PasswordBundle\Model\ResetMailerInterface;
use Octave\PasswordBundle\Model\ResetUserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PasswordChangeSubscriber implements EventSubscriberInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var integer
     */
    private $expirationDays;

    /**
     * @var string
     */
    private $redirectRouteName;

    /**
     * @var bool
     */
    private $sendResetEmail;

    /**
     * @var ResetMailerInterface
     */
    private $mailer;

    /**
     * PasswordChangeSubscriber constructor.
     * @param UrlGeneratorInterface $router
     * @param TokenStorageInterface $tokenStorage
     * @param $expirationDays
     * @param $redirectRouteName
     * @param $sendResetEmail
     * @param ResetMailerInterface $mailer
     */
    public function __construct(UrlGeneratorInterface $router, TokenStorageInterface $tokenStorage, $expirationDays,
                                                      $redirectRouteName, $sendResetEmail)
    {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->expirationDays = $expirationDays;
        $this->redirectRouteName = $redirectRouteName;
        $this->sendResetEmail = $sendResetEmail;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }

    /**
     * @param ResetMailerInterface $mailer
     */
    public function setMailer($mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) return;

        $user = $token->getUser();
        if (!$user) return;

        $routeName = $event->getRequest()->get('_route');
        if (in_array($routeName, [$this->redirectRouteName, ''])) {
            return;
        }

        if ($user instanceof ResetUserInterface && $user->isPasswordExpired($this->expirationDays)) {

            if ($this->sendResetEmail && $this->mailer instanceof ResetMailerInterface) {
                $user->setPasswordChangeToken($user->generateToken());
                $this->mailer->sendReset($user);
            }

            $event->setResponse(new RedirectResponse($this->router->generate($this->redirectRouteName)));
        }
    }
}
