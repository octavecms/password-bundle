<?php

namespace Octave\PasswordBundle\DependencyInjection\Compiler;

use Octave\PasswordBundle\EventListener\AdminAuthSubscriber;
use Octave\PasswordBundle\EventListener\PasswordChangeSubscriber;
use Octave\PasswordBundle\Service\UserInviteService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MailerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $subscriberClasses = [
            PasswordChangeSubscriber::class,
            AdminAuthSubscriber::class,
            UserInviteService::class,
        ];

        foreach ($subscriberClasses as $subscriberClass) {
            if (!$container->has($subscriberClass)) {
                continue;
            }

            $definition = $container->findDefinition($subscriberClass);
            $mailerId = $container->getParameter('octave.password.mailer.class');
            $definition->addMethodCall('setMailer', [new Reference($mailerId)]);
        }
    }
}