<?php

namespace Octave\PasswordBundle;

use Octave\PasswordBundle\DependencyInjection\Compiler\MailerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OctavePasswordBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new MailerPass());
    }
}