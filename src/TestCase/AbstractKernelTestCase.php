<?php

namespace SoureCode\BundleTest\TestCase;

use SoureCode\BundleTest\Configurator\KernelConfigurator;
use SoureCode\BundleTest\Exception\RuntimeException;
use SoureCode\BundleTest\Kernel\TestKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractKernelTestCase extends KernelTestCase
{
    protected static ?KernelConfigurator $kernelConfigurator = null;

    protected static function createKernel(array $options = []): KernelInterface
    {
        if (!static::$kernelConfigurator) {
            throw new RuntimeException('KernelConfigurator is not set up properly.');
        }

        return static::$kernelConfigurator->build();
    }

    protected function setUp(): void
    {
        static::$kernelConfigurator = new KernelConfigurator();
    }

    protected function tearDown(): void
    {
        if (static::$booted) {
            if (static::$kernel instanceof TestKernel) {
                static::$kernel->clear();
            }
        }

        parent::tearDown();

        static::$kernelConfigurator = null;
    }

    protected function runCommand(array $command): void
    {
        if (!static::$booted) {
            throw new RuntimeException('Kernel musst be booted before run command.');
        }

        $application = new Application(static::$kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput($command);

        $output = new BufferedOutput();
        $exitCode = $application->run($input, $output);

        if (0 !== $exitCode) {
            $content = $output->fetch();
            $consoleException = new RuntimeException($content);

            throw new RuntimeException(sprintf('Could not run command "%s".', serialize($command)), $exitCode, $consoleException);
        }
    }
}