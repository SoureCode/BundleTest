<?php

namespace SoureCode\BundleTest\TestCase;

abstract class AbstractDoctrineTestCase extends AbstractKernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (static::$kernelConfigurator) {
            $mappings = $this->getDoctrineMappings();
            $migrations = $this->getDoctrineMigrations();

            static::$kernelConfigurator->setDoctrine($mappings);
            static::$kernelConfigurator->setDoctrineMigrations($migrations);
        }
    }

    abstract protected function getDoctrineMappings(): array;

    abstract protected function getDoctrineMigrations(): array;

    protected function clearDatabase(): void
    {
        $this->runCommand(
            [
                'command' => 'doctrine:database:drop',
                '--force' => true,
            ]
        );

        $this->runCommand(
            [
                'command' => 'doctrine:database:create',
            ]
        );
    }

    protected function prepareDatabase(): void
    {
        $this->runCommand(
            [
                'command' => 'doctrine:migrations:migrate',
                '--no-interaction' => true,
            ]
        );
    }
}
