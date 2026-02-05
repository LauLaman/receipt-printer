<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Contract;

interface PrinterTransportInterface
{
    public function write(string $data): void;
}
