<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Infrastructure\Transport;

use LauLaman\ReceiptPrinter\Domain\Contract\PrinterTransportInterface;
use LauLaman\ReceiptPrinter\Infrastructure\Php\ExecRunner;

final class IppTransportInterface implements PrinterTransportInterface
{
    private string $uri;

    public function __construct(
        string $serial,
        private ExecRunner $execRunner = new ExecRunner(),
    ) {
        $this->uri = "usb://Star/mC-Print2?serial={$serial}";
    }

    public function write(string $data): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'print_');
        file_put_contents($tmpFile, $data);

        $this->execRunner->run("lpr -H {$this->uri} -o raw {$tmpFile}", $output, $returnCode);

        unlink($tmpFile);

        if ($returnCode !== 0) {
            throw new \RuntimeException("Print failed: " . implode("\n", $output));
        }
    }
}


