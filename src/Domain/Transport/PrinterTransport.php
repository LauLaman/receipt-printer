<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Transport;

interface PrinterTransport
{
    public function write(string $data): void;
}
