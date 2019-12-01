<?php

declare(strict_types=1);

namespace Rector\Compiler\Tests\Console;

use PHPUnit\Framework\TestCase;
use Rector\Compiler\Contract\Filesystem\FilesystemInterface;
use Rector\Compiler\Contract\Process\Process;
use Rector\Compiler\Contract\Process\ProcessFactoryInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class CompileCommandTest extends TestCase
{
    public function testCommand(): void
    {
        $filesystem = $this->createMock(FilesystemInterface::class);
        $filesystem->expects(self::once())->method('read')->with('bar/composer.json')->willReturn(
            '{"name":"rector/rector-src","replace":{"rector/rector": "self.version"},"require":{"php":"~7.1"},"require-dev":1,"autoload-dev":2,"autoload":{"psr-4":{"Rector\\\\":[3]}}}'
        );
        $filesystem->expects(self::once())->method('write')->with('bar/composer.json', <<<EOT
{
    "name": "rector/rector",
    "require": {
        "php": "~7.1"
    },
    "autoload": {
        "psr-4": {
            "Rector\\\\": "src/"
        }
    }
}
EOT
        );

        $process = $this->createMock(Process::class);

        $processFactory = $this->createMock(ProcessFactoryInterface::class);
        $processFactory->expects(self::at(0))->method('setOutput');
        $processFactory->expects(self::at(1))->method('create')->with(
            ['composer', 'require', '--no-update', 'dg/composer-cleaner:^2.0'],
            'bar'
        )->willReturn($process);
        $processFactory->expects(self::at(2))->method('create')->with(
            ['composer', 'update', '--no-dev', '--classmap-authoritative'],
            'bar'
        )->willReturn($process);
        $processFactory->expects(self::at(3))->method('create')->with(
            ['php', 'box.phar', 'compile'],
            'foo'
        )->willReturn($process);

        $application = new Application();
        $application->add(new CompileCommand($filesystem, $processFactory, 'foo', 'bar'));

        $command = $application->find('rector:compile');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        self::assertSame('', $commandTester->getDisplay());
    }
}
