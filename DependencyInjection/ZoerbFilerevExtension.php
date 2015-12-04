<?php

namespace Zoerb\Bundle\FilerevBundle\DependencyInjection;

use Symfony\Bundle\FrameworkBundle\DependencyInjection\Configuration as FrameworkConfiguration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
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

        $defaultPackage = $this->createPackageDefinition($container, $config);


        // overwrite symfony default package
        $container->setDefinition('templating.asset.default_package', $defaultPackage);

    }

    /**
     * Returns a definition for an asset package.
     *
     * @param ContainerBuilder $container Container
     * @param array            $config    bundle config
     * @param string           $name      Package name
     *
     * @return DefinitionDecorator
     */
    private function createPackageDefinition(ContainerBuilder $container, array $config, $name = null)
    {

        // assets_base_urls are cloned from framework configuration in `prepend`
        $httpUrls = $config['assets_base_urls']['http'];
        $sslUrls = $config['assets_base_urls']['ssl'];
        $rootDir = $config['root_dir'];
        $length = $config['length'];
        $summaryFile = $config['summary_file'];
        $debug = $config['debug'];
        $cacheDir = $container->getParameter('kernel.cache_dir').'/zoerb_filerev';


        if (!$httpUrls) {
            $package = new DefinitionDecorator('zoerb_filerev.templating.asset.path_package');
            $package
                ->setPublic(false)
                ->setScope('request')
                ->replaceArgument(1, $rootDir)
                ->replaceArgument(2, $summaryFile)
                ->replaceArgument(3, $cacheDir)
                ->replaceArgument(4, $length)
                ->replaceArgument(5, $debug);

            return $package;
        }

        if ($httpUrls == $sslUrls) {
            $package = new DefinitionDecorator('zoerb_filerev.templating.asset.url_package');

            $package
                ->setPublic(false)
                ->replaceArgument(0, $sslUrls)
                ->replaceArgument(1, $rootDir)
                ->replaceArgument(2, $summaryFile)
                ->replaceArgument(3, $cacheDir)
                ->replaceArgument(4, $length)
                ->replaceArgument(5, $debug);

            return $package;
        }

        $prefix = $name ? 'templating.asset.package.'.$name : 'templating.asset.default_package';

        $httpPackage = new DefinitionDecorator('zoerb_filerev.templating.asset.url_package');
        $httpPackage
            ->replaceArgument(0, $httpUrls)
            ->replaceArgument(1, $rootDir)
            ->replaceArgument(2, $summaryFile)
            ->replaceArgument(3, $cacheDir)
            ->replaceArgument(4, $length)
            ->replaceArgument(5, $debug);
        $container->setDefinition($prefix.'.http', $httpPackage);

        if ($sslUrls) {
            $sslPackage = new DefinitionDecorator('zoerb_filerev.templating.asset.url_package');
            $sslPackage
                ->replaceArgument(0, $sslUrls)
                ->replaceArgument(1, $rootDir)
                ->replaceArgument(2, $summaryFile)
                ->replaceArgument(3, $cacheDir)
                ->replaceArgument(4, $length)
                ->replaceArgument(5, $debug);
        } else {
            $sslPackage = new DefinitionDecorator('zoerb_filerev.templating.asset.path_package');
            $sslPackage
                ->setScope('request')
                ->replaceArgument(1, $rootDir)
                ->replaceArgument(2, $summaryFile)
                ->replaceArgument(3, $cacheDir)
                ->replaceArgument(4, $length)
                ->replaceArgument(5, $debug);
        }
        $container->setDefinition($prefix.'.ssl', $sslPackage);


        $package = new DefinitionDecorator('templating.asset.request_aware_package');
        $package
            ->setPublic(false)
            ->setScope('request')
            ->replaceArgument(1, $prefix.'.http')
            ->replaceArgument(2, $prefix.'.ssl');

        return $package;
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

        // check if assets_base_urls is set in the "templating" configuration
        if (isset($config['templating']) && isset($config['templating']['assets_base_urls'])) {
            // prepend the acme_something settings with the entity_manager_name
            $innerConfig = array('assets_base_urls' => $config['templating']['assets_base_urls']);
            $container->prependExtensionConfig($this->getAlias(), $innerConfig);
        }
    }
}
