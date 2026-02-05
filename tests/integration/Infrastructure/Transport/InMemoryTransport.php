<?php

declare(strict_types=1);

namespace Tests\Integration\LauLaman\ReceiptPrinter\Infrastructure\Transport;

use LauLaman\ReceiptPrinter\Domain\Contract\PrinterTransportInterface;

final class InMemoryTransport implements PrinterTransportInterface
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