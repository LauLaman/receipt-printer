<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Infrastructure\Transport;

use LauLaman\ReceiptPrinter\Domain\Contract\PrinterTransportInterface;
use LauLaman\ReceiptPrinter\Infrastructure\Php\ProcessRunner;

final class CupsTransportInterface implements PrinterTransportInterface
{
    private string $printerName;

    public function __construct(string $printerName, private ProcessRunner $runner = new ProcessRunner())
    {
        $this->printerName = $printerName;
    }

    public function write(string $data): void
    {
        $descriptors = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w']   // stderr
        ];

        $pipes = [];
        $process = $this->runner->run("lpr -P {$this->printerName} -o raw", $descriptors, $pipes);

        if (!is_resource($process)) {
            throw new \RuntimeException("Failed to open CUPS printer");
        }

        fwrite($pipes[0], $data);
        fclose($pipes[0]);

        $this->runner->close($process);
    }
}

