<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Draw;

use LauLaman\ReceiptPrinter\Domain\Command\Barcode\BarcodeCommand;
use LauLaman\ReceiptPrinter\Domain\Enum\BarcodeType;

final readonly class Barcode implements DrawCommand
{
    public function __construct(
        public string $data,
        public BarcodeType $type = BarcodeType::CODE39,
        public int $width = 2,
        public int $height = 162,
        public bool $hri = true
    ) {}
}