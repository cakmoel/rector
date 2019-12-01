<?php

declare(strict_types=1);

namespace Rector\Compiler\Console;

use Nette\Utils\FileSystem as NetteFileSystem;
use Nette\Utils\Json;
use Rector\Compiler\Contract\Process\ProcessFactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

final class CompileCommand extends Command
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ProcessFactoryInterface
     */
    private $processFactory;

    /**
     * @var string
     */
    private $dataDir;

    /**
     * @var string
     */
    private $buildDir;

    public function __construct(ProcessFactoryInterface $processFactory, string $dataDir, string $buildDir)
    {
        parent::__construct();
        $this->filesystem = new Filesystem();
        $this->processFactory = $processFactory;
        $this->dataDir = $dataDir;
        $this->buildDir = $buildDir;
    }

    protected function configure(): void
    {
        $this->setName('rector:compile');
        $this->setDescription('Compile prefixed rector.phar');
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
        $fileContent = NetteFileSystem::read($buildDir . '/composer.json');
        $json = Json::decode($fileContent, Json::FORCE_ARRAY);

        // remove dev dependencies (they create conflicts)
        unset($json['require-dev'], $json['autoload-dev']);

        unset($json['replace']);

        $json['name'] = 'rector/rector';

        // simplify autoload (remove not packed build directory]
        $json['autoload']['psr-4']['Rector\\'] = 'src';

        $encodedJson = Json::encode($json, Json::PRETTY);

        $this->filesystem->dumpFile($buildDir . '/composer.json', $encodedJson);
    }
}
