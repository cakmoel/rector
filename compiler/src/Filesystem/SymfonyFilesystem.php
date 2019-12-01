<?php

declare(strict_types=1);

namespace Rector\Compiler\Filesystem;

use Nette\Utils\FileSystem as NetteFileSystem;
use Rector\Compiler\Contract\Filesystem\FilesystemInterface;
use Symfony\Component\Filesystem\Filesystem;

final class SymfonyFilesystem implements FilesystemInterface
{
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function exists(string $dir): bool
    {
        return $this->filesystem->exists($dir);
    }

    public function remove(string $dir): void
    {
        $this->filesystem->remove($dir);
    }

    public function mkdir(string $dir): void
    {
        $this->filesystem->mkdir($dir);
    }

    public function read(string $file): string
    {
        return NetteFileSystem::read($file);
    }

    public function write(string $file, string $data): void
    {
        file_put_contents($file, $data);
    }
}
