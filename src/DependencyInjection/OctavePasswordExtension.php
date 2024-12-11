<?php

namespace Octave\PasswordBundle\DependencyInjection;

use Octave\PasswordBundle\Model\AdminAuthMailerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Octave\PasswordBundle\Model\ResetMailerInterface;

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

        $container->setAlias(ResetMailerInterface::class, $config['mailer_class']);
        $container->setAlias(AdminAuthMailerInterface::class, $config['mailer_class']);

        $container->setParameter('octave.password.redirect.route', $config['redirect_route_name']);
        $container->setParameter('octave.password.admin.prefix', $config['admin_prefix_name']);

        $container->setParameter('octave.password.send.email', $config['send_email']);
        $container->setParameter('octave.password.mailer.class', $config['mailer_class'] ?? null);
        $container->setParameter('octave.password.user.class', $config['user_class'] ?? null);
        $container->setParameter('octave.password.ask.current.password', $config['ask_current_password']);

        $container->setParameter('octave.password.reset.token.lifetime', $config['reset_password']['token_lifetime']);
        $container->setParameter('octave.password.reset.resend.interval', $config['reset_password']['resend_interval']);

        $container->setParameter('octave.password.min.length', $config['password']['min_length']);
        $container->setParameter('octave.password.max.length', $config['password']['max_length']);
        $container->setParameter('octave.password.complexity.level', $config['password']['complexity_level']);
        $container->setParameter('octave.password.expiration.days', $config['password']['expiration_days']);
        $container->setParameter('octave.password.keep.history', $config['password']['keep_history']);
        $container->setParameter('octave.password.history.count', $config['password']['history_count']);

        $container->setParameter('octave.admin_auth.require.confirmation', $config['admin_auth']['require_confirmation']);
        $container->setParameter('octave.admin_auth.confirmation.code.lifetime', $config['admin_auth']['confirmation_code_lifetime']);

    }
}
