<?php declare(strict_types = 1);

namespace Rector\Compiler\Console;

use Nette\Utils\Json;
use Rector\Compiler\Filesystem\Filesystem;
use Rector\Compiler\Process\ProcessFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CompileCommand extends Command
{

	/** @var Filesystem */
	private $filesystem;

	/** @var ProcessFactory */
	private $processFactory;

	/** @var string */
	private $dataDir;

	/** @var string */
	private $buildDir;

	public function __construct(
		Filesystem $filesystem,
		ProcessFactory $processFactory,
		string $dataDir,
		string $buildDir
	)
	{
		parent::__construct();
		$this->filesystem = $filesystem;
		$this->processFactory = $processFactory;
		$this->dataDir = $dataDir;
		$this->buildDir = $buildDir;
	}

	protected function configure(): void
	{
		$this->setName('rector:compile')
			->setDescription('Compile PHAR');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->processFactory->setOutput($output);

        // this breaks phpstan dependency, as whole /conf is removed
//		$this->processFactory->create(['composer', 'require', '--no-update', 'dg/composer-cleaner:^2.0'], $this->buildDir);
        // exclude this with "clearn ignore: https://github.com/dg/composer-cleaner#configuration + uncomment and try again :)

		$this->fixComposerJson($this->buildDir);
		$this->processFactory->create(['composer', 'update', '--no-dev', '--classmap-authoritative'], $this->buildDir);

		$this->processFactory->create(['php', 'box.phar', 'compile'], $this->dataDir);

		return 0;
	}

	private function fixComposerJson(string $buildDir): void
	{
	    $fileContent = $this->filesystem->read($buildDir . '/composer.json');
		$json = Json::decode($fileContent, Json::FORCE_ARRAY);

		// remove dev dependencies (they create conflicts)
		unset($json['require-dev'], $json['autoload-dev']);

		unset($json['replace']);

		$json['name'] = 'rector/rector';

		// simplify autoload (remove not packed build directory]
		$json['autoload']['psr-4']['Rector\\'] = 'src';

		$encodedJson =  Json::encode($json, Json::PRETTY);

		$this->filesystem->write($buildDir . '/composer.json', $encodedJson);
	}

}
