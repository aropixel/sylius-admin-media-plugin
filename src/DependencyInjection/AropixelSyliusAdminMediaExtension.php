<?php

namespace Aropixel\SyliusAdminMediaPlugin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AropixelSyliusAdminMediaExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {

        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $this->registerParameters($container, $config);

        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('services.xml');

    }

    private function registerParameters(ContainerBuilder $container, array $config)
    {
        $container->setParameter('aropixel_sylius_admin_media.entities_crops', $config['entities_crops']);
        $container->setParameter('aropixel_sylius_admin_media.default_crops', $config['default_crops']);
    }

    public function prepend(ContainerBuilder $container)
    {
        $this->loadBundlesTemplatesOverrides( $container );

        $this->createLiipFilters( $container );

    }

    /**
     * @param ContainerBuilder $container
     *
     */
    private function createLiipFilters( ContainerBuilder $container )
    {
        $bundles = $container->getParameter( 'kernel.bundles' );

        $config  = [
            'square' => [
                'quality' => 75,
                'filters' => [
                    'thumbnail' => [
                        'size' => [600, 600],
                        'mode'=> 'outbound',
                        'allow_upscale' => true
                    ],
                ]
            ],
            'portrait'   => [
                'quality' => 75,
                'filters' => [
                    'thumbnail' => [
                        'size' => [540, 675],
                        'mode'=> 'outbound',
                        'allow_upscale' => true
                    ],
                ]
            ],
            'landscape' => [
                'quality' => 75,
                'filters' => [
                    'thumbnail' => [
                        'size' => [1080, 720],
                        'mode'=> 'outbound',
                        'allow_upscale' => true
                    ],
                ]
            ]
        ];

        $sizes = [ 100, 200, 300, 400, 600, 800 ];

        foreach ( $sizes as $width ) {
            $config[ 'editor_' . $width ] = [
                'quality' => 75,
                'filters' => [
                    'relative_resize' => [
                        'widen' => $width
                    ],
                ]
            ];
        }

        $config['editor_100pc'] = [
            'quality' => 75,
            'filters' => [
                'relative_resize' => [
                    'widen' => 1200
                ],
            ]
        ];

        $config['auto'] = [
            'quality' => 75,
            'filters' => [
                'relative_resize' => [
                    'widen' => 100
                ],
            ]
        ];

        $container->prependExtensionConfig('liip_imagine', ['filter_sets' => $config]);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadBundlesTemplatesOverrides( ContainerBuilder $container ): void
    {
        $rootBundle = dirname( __FILE__, 2 );

        $container->loadFromExtension( 'twig', [
            'paths' => [
                $rootBundle . '/Resources/views/ArtgrisFileManagerBundle' => 'ArtgrisFileManager',
                $rootBundle . '/Resources/views/ArtgrisMediaBundle' => 'ArtgrisMedia',
                $rootBundle . '/Resources/views/SyliusAdminBundle'        => 'SyliusAdmin',
                $rootBundle . '/Resources/views/SyliusUiBundle'           => 'SyliusUi',
            ]
        ] );
    }


}
