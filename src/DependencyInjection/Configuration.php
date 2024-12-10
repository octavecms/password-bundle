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
                ->scalarNode('redirect_route_name')->isRequired()->end()
                ->booleanNode('send_email')->end()
                ->scalarNode('mailer_class')->end()
                ->scalarNode('user_class')->isRequired()->end()
                ->booleanNode('ask_current_password')->end()
            ->arrayNode('reset_password')
                ->children()
                    ->integerNode('token_lifetime')
                    ->end()
                    ->integerNode('resend_interval')
                    ->end()
                ->end()
            ->end()
            ->arrayNode('password')
                ->children()
                    ->integerNode('min_length')
                    ->end()
                    ->integerNode('max_length')
                    ->end()
                    ->scalarNode('complexity_level')
                    ->end()
                    ->integerNode('expiration_days')
                    ->end()
                    ->booleanNode('keep_history')
                    ->end()
                    ->integerNode('history_count')
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
