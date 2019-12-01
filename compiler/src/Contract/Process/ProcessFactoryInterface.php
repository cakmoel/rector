<?php

declare(strict_types=1);

namespace Rector\Compiler\Contract\Process;

use Symfony\Component\Console\Output\OutputInterface;

interface ProcessFactoryInterface
{
    /**
     * @param string[] $command
     */
    public function create(array $command, string $cwd): ProcessInterface;

    public function setOutput(OutputInterface $output): void;
}
