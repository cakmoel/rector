<?php declare(strict_types = 1);

namespace Rector\Compiler\Process;

use Symfony\Component\Console\Output\OutputInterface;

interface ProcessFactory
{

	/**
	 * @param string[] $command
	 * @param string $cwd
	 * @return \Rector\Compiler\Process\Process
	 */
	public function create(array $command, string $cwd): Process;

	public function setOutput(OutputInterface $output): void;

}
