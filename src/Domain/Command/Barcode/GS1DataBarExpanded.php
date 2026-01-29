<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Barcode;

final readonly class GS1DataBarExpanded implements BarcodeCommand
{
    public function __construct(
        public string $data
    ) {}
}
