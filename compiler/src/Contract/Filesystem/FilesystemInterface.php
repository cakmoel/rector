<?php

declare(strict_types=1);

namespace Rector\Compiler\Contract\Filesystem;

interface FilesystemInterface
{
    public function exists(string $dir): bool;

    public function remove(string $dir): void;

    public function mkdir(string $dir): void;

    public function read(string $file): string;

    public function write(string $file, string $data): void;
}
