<?php

namespace Octave\ToolsBundle\DependencyInjection;

use Octave\PasswordBundle\DependencyInjection\Configuration;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class OctavePasswordExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('octave.password.lifetime', $config['password_lifetime'] ?? 90);
        $container->setParameter('octave.password.redirect.route', $config['redirect_route_name'] ?? null);
        $container->setParameter('octave.password.send.email', $config['send_email'] ?? false);
        $container->setParameter('octave.password.mailer.class', $config['mailer_class'] ?? null);
        $container->setParameter('octave.password.user.class', $config['user_class'] ?? null);
        $container->setParameter('octave.password.ask.current.password', $config['ask_current_password'] ?? false);
        $container->setParameter('octave.password.min.length', $config['password_min_len'] ?? 8);
        $container->setParameter('octave.password.max.length', $config['password_max_len'] ?? 30);
        $container->setParameter('octave.password.requirements', $config['requirements'] ?? []);
    }
}
