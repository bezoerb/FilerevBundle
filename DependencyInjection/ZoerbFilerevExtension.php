<?php

/**
 * Copyright (c) 2015 Ben Zörb
 * Licensed under the MIT license.
 * http://bezoerb.mit-license.org/
 */

namespace Zoerb\Bundle\FilerevBundle\DependencyInjection;

use Symfony\Bundle\FrameworkBundle\DependencyInjection\Configuration as FrameworkConfiguration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ZoerbFilerevExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('parameters.yml');
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // skip if not enabled
        if (!$config['enabled']) {
            return;
        }

        $defaultVersion = $this->createVersion($container, $config, '_default');
        $defaultPackage = $this->createPackageDefinition($config['base_path'], $config['base_urls'], $defaultVersion);

        $container->setDefinition('assets._default_package', $defaultPackage);
    }


    /**
     * Returns a definition for an asset package.
     */
    private function createPackageDefinition($basePath, array $baseUrls, Reference $version)
    {
        if ($basePath && $baseUrls) {
            throw new \LogicException('An asset package cannot have base URLs and base paths.');
        }

        if (!$baseUrls) {
            $package = new DefinitionDecorator('assets.path_package');

            return $package
                ->setPublic(false)
                ->replaceArgument(0, $basePath)
                ->replaceArgument(1, $version)
                ;
        }

        $package = new DefinitionDecorator('assets.url_package');

        return $package
            ->setPublic(false)
            ->replaceArgument(0, $baseUrls)
            ->replaceArgument(1, $version)
            ;
    }

    /**
     * Returns version strategy reference
     *
     * @param ContainerBuilder $container
     * @param array            $config
     * @param                  $name
     *
     * @return Reference
     */
    private function createVersion(ContainerBuilder $container, array $config, $name)
    {
        // $rootDir, $summaryFile, $hashLength, $cacheDir, $debug)
        $rootDir = $config['root_dir'];
        $summaryFile = $config['summary_file'];
        $hashLength = $config['length'];
        $cacheDir = $container->getParameter('kernel.cache_dir').'/zoerb_filerev';
        $debug = $config['debug'];

        $def = new DefinitionDecorator('zoerb_filerev.assets.json_version_strategy');
        $def
            ->replaceArgument(0, $rootDir)
            ->replaceArgument(1, $summaryFile)
            ->replaceArgument(2, $hashLength)
            ->replaceArgument(3, $cacheDir)
            ->replaceArgument(4, $debug)
        ;
        $container->setDefinition('assets._version_'.$name, $def);

        return new Reference('assets._version_'.$name);
    }

    /**
     * Set assets_base_urls configuration from framework config
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        // process the configuration of FrameworkExtension
        $configs = $container->getExtensionConfig('framework');

        // use the FrameworkConfiguration class to generate a config
        $config = $this->processConfiguration(new FrameworkConfiguration(false), $configs);

        if (isset($config['assets'])) {
            $frameworkConfig = array();

            if (isset($config['assets']['base_urls'])) {
                $frameworkConfig['base_urls'] = $config['assets']['base_urls'];
            }

            if (isset($config['assets']['base_path'])) {
                $frameworkConfig['base_path'] = $config['assets']['base_path'];
            }

            $container->prependExtensionConfig($this->getAlias(), $frameworkConfig);
        }
    }
}
