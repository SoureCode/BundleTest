<?php

namespace SoureCode\BundleTest\Configurator;

use function array_key_exists;
use DAMA\DoctrineTestBundle\DAMADoctrineTestBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use SoureCode\BundleTest\Exception\RuntimeException;
use SoureCode\BundleTest\Kernel\TestKernel;
use Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class KernelConfigurator
{
    private array $bundleConfigurations = [
        FrameworkBundle::class => [
            'framework',
            [
                [
                    'secret' => 'foo',
                    'router' => ['utf8' => true],
                    'default_locale' => 'en',
                ],
            ],
        ],
    ];

    private array $routeFiles = [];
    private array $serviceFiles = [];

    public function addServiceFile(string $file)
    {
        $this->serviceFiles[] = $file;
    }

    public function addRouteFile(string $file)
    {
        $this->routeFiles[] = $file;
    }

    public function setDAMA(): void
    {
        $this->setBundle(
            DAMADoctrineTestBundle::class,
            'dama_doctrine_test'
        );
    }

    /**
     * @param class-string<Bundle> $bundle
     */
    public function setBundle(string $bundle, string $namespace = null, array $configuration = []): void
    {
        $this->bundleConfigurations[$bundle] = [
            $namespace,
            [$configuration],
        ];
    }

    /**
     * @param class-string<Bundle> $bundle
     */
    public function extend(string $bundle, array $configuration): void
    {
        if (!array_key_exists($bundle, $this->bundleConfigurations)) {
            throw new RuntimeException('You musst set the bundle first, before you can extend it.');
        }

        $config = $this->bundleConfigurations[$bundle][1];

        $this->bundleConfigurations[$bundle][1] = array_merge($config, [$configuration]);
    }

    public function setDoctrine(array $mappings = []): void
    {
        $this->setBundle(
            DoctrineBundle::class,
            'doctrine',
            [
                'dbal' => [
                    'url' => '%env(DATABASE_URL)%',
                ],
                'orm' => [
                    'auto_generate_proxy_classes' => true,
                    'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
                    'auto_mapping' => true,
                    'mappings' => $mappings,
                ],
            ]
        );
    }

    public function setDoctrineMigrations(array $migrations = []): void
    {
        $this->setBundle(
            DoctrineMigrationsBundle::class,
            'doctrine_migrations',
            [
                'migrations_paths' => $migrations,
            ]
        );
    }

    public function build(): TestKernel
    {
        return new TestKernel($this->bundleConfigurations, $this->routeFiles, $this->serviceFiles);
    }
}
