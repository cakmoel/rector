<?php

declare(strict_types=1);

namespace Rector\Compiler\Tests\Process;

use PHPUnit\Framework\TestCase;
use Rector\Compiler\Process\SymfonyProcess;
use Symfony\Component\Console\Output\OutputInterface;

final class SymfonyProcessTest extends TestCase
{
    public function testGetProcess(): void
    {
        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())->method('write');

        $process = (new SymfonyProcess(['ls'], __DIR__, $output))->getProcess();

        $this->assertSame('\'ls\'', $process->getCommandLine());
        $this->assertSame(__DIR__, $process->getWorkingDirectory());
    }
}
