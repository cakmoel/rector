<?php

declare(strict_types=1);

namespace Rector\Compiler\Contract\Process;

use Symfony\Component\Process\Process;

interface ProcessInterface
{
    /**
     * @return Process<string, string>
     */
    public function getProcess(): Process;
}
