<?php

namespace Octave\PasswordBundle\DependencyInjection\Compiler;

use Octave\PasswordBundle\EventListener\PasswordChangeSubscriber;
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
        if (!$container->has(PasswordChangeSubscriber::class)) {
            return;
        }

        $definition = $container->findDefinition(PasswordChangeSubscriber::class);
        $id = $container->getParameter('octave.password.mailer.class');
        $definition->addMethodCall('setMailer', array(new Reference($id)));
    }
}