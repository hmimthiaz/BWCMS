<?php

namespace Bellwether\BWCMSBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class BWCMSExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('media.path', $config['media']['path']);
        $container->setParameter('media.maxUploadSize', $config['media']['maxUploadSize']);
        $container->setParameter('media.maxUploadImageSize', $config['media']['maxUploadImageSize']);
        $container->setParameter('media.blockedExtension', $config['media']['blockedExtension']);

        $container->setParameter('media.transport', $config['media']['transport']);
        $container->setParameter('media.s3Enabled', $config['media']['s3Enabled']);
        $container->setParameter('media.s3Prefix', $config['media']['s3Prefix']);
        $container->setParameter('media.s3Bucket', $config['media']['s3Bucket']);
        $container->setParameter('media.s3DomainURLPrefix', $config['media']['s3DomainURLPrefix']);

        $container->setParameter('media.s3SkinEnabled', $config['media']['s3SkinEnabled']);
        $container->setParameter('media.s3SkinURLPrefix', $config['media']['s3SkinURLPrefix']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

    }

    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        if (true === isset($bundles['TwigBundle'])) {
            $this->configureTwigBundle($container);
        }
    }

    function configureTwigBundle(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('twig', array(
                'form' => array(
                    'resources' => array(
                        'BWCMSBundle:Form:bwcms.html.twig'
                    )
                )
            )
        );
    }

}
