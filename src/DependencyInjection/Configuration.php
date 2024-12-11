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
                ->scalarNode('admin_prefix_name')->defaultValue('/admin')->end()
                ->booleanNode('send_email')->defaultFalse()->end()
                ->scalarNode('mailer_class')->end()
                ->scalarNode('user_class')->isRequired()->end()
                ->booleanNode('ask_current_password')->defaultFalse()->end()
            ->arrayNode('reset_password')
                ->children()
                    ->integerNode('token_lifetime')->defaultValue(60)->end()
                    ->integerNode('resend_interval')->defaultValue(15)->end()
                ->end()
            ->end()
            ->arrayNode('password')
                ->children()
                    ->integerNode('min_length')->defaultValue(8)->end()
                    ->integerNode('max_length')->defaultValue(25)->end()
                    ->scalarNode('complexity_level')->defaultValue('easy')->end()
                    ->integerNode('expiration_days')->defaultValue(90)->end()
                    ->booleanNode('keep_history')->defaultValue('no')->end()
                    ->integerNode('history_count')->defaultValue(0)->end()
                ->end()
            ->end()
            ->arrayNode('admin_auth')
                ->children()
                    ->booleanNode('require_confirmation')->defaultFalse()->end()
                    ->integerNode('confirmation_code_lifetime')->defaultValue(15)->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
