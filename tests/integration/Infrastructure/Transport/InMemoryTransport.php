<?php

declare(strict_types=1);

namespace Tests\Integration\LauLaman\ReceiptPrinter\Infrastructure\Transport;

use LauLaman\ReceiptPrinter\Domain\Transport\PrinterTransport;

final class InMemoryTransport implements PrinterTransport
{
    private string $data = '';

    public function write(string $data): void
    {
        $this->data .= $data;
    }

    public function getData(): string
    {
        return $this->data;
    }
}