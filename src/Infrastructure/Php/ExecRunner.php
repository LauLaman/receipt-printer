<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Infrastructure\Php;

class ExecRunner
{
    /**
     * Executes a system command
     *
     * @param string $command
     * @param array<int, string> $output
     * @param int $returnVar
     */
    public function run(string $command, array &$output = null, int &$returnVar = null): void
    {
        exec($command, $output, $returnVar);
    }
}