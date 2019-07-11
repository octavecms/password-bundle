<?php

namespace Octave\PasswordBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('octave_password');

        $treeBuilder
            ->getRootNode()
            ->children()
            ->scalarNode('password_lifetime')->end()
            ->scalarNode('redirect_route_name')->isRequired()->end()
            ->booleanNode('send_email')->end()
            ->scalarNode('mailer_class')->end()
            ->scalarNode('user_class')->isRequired()->end()
            ->booleanNode('ask_current_password')->end()
            ->integerNode('password_min_len')->end()
            ->integerNode('password_max_len')->end()
            ->arrayNode('requirements')
            ->prototype('array')
                ->prototype('scalar')->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
