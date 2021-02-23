<?php

namespace SoureCode\BundleTest\Kernel;

use const GLOB_MARK;
use function is_array;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    private array $bundleConfigurations;

    private array $routeFiles;

    public function __construct(array $bundleConfigurations, array $routeFiles)
    {
        parent::__construct('test', true);

        $this->bundleConfigurations = $bundleConfigurations;
        $this->routeFiles = $routeFiles;
    }

    public function clear(): void
    {
        $this->delete_files($this->getProjectDir().'/var');
    }

    private function delete_files(string $target): void
    {
        if (is_dir($target)) {
            $files = glob($target.'*', GLOB_MARK); //GLOB_MARK adds a slash to directories returned

            if ($files) {
                foreach ($files as $file) {
                    $this->delete_files($file);
                }
            }

            if (file_exists($target)) {
                rmdir($target);
            }
        } elseif (is_file($target) && file_exists($target)) {
            unlink($target);
        }
    }

    public function registerBundles(): array
    {
        $bundles = [];

        /**
         * @var array<class-string<Bundle>> $bundleClasses
         */
        $bundleClasses = array_keys($this->bundleConfigurations);

        foreach ($bundleClasses as $class) {
            $bundles[] = new $class();
        }

        return $bundles;
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        foreach ($this->bundleConfigurations as [$namespace, $config]) {
            $configs = is_array($config) ? $config : [$config];

            if (null !== $namespace) {
                foreach ($configs as $conf) {
                    $container->extension($namespace, $conf);
                }
            }
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        foreach ($this->routeFiles as $routeFile) {
            if (is_file($path = $routeFile)) {
                (require $path)($routes->withPath($path), $this);
            }
        }
    }
}
