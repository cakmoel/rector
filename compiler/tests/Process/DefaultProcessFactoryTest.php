<?php

declare(strict_types=1);

namespace Rector\Compiler\Tests\Process;

use PHPUnit\Framework\TestCase;
use Rector\Compiler\Process\DefaultProcessFactory;
use Symfony\Component\Console\Output\OutputInterface;

final class DefaultProcessFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $output = $this->createMock(OutputInterface::class);
        $output->expects(self::once())->method('write');

        $factory = new DefaultProcessFactory();
        $factory->setOutput($output);

        $process = $factory->create(['ls'], __DIR__)->getProcess();
        self::assertSame('\'ls\'', $process->getCommandLine());
        self::assertSame(__DIR__, $process->getWorkingDirectory());
    }
}
