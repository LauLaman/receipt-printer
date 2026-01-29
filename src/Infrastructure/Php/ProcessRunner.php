<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Infrastructure\Php;

class ProcessRunner
{
    /**
     * Run a process with pipes
     *
     * @param string $command
     * @param array<int, mixed> $descriptors
     * @param array<int, resource> $pipes
     * @return resource|false
     */
    public function run(string $command, array $descriptors, array &$pipes)
    {
        return proc_open($command, $descriptors, $pipes);
    }

    /**
     * Close a process resource
     */
    public function close($process): void
    {
        proc_close($process);
    }
}
