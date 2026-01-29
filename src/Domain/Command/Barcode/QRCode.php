<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Barcode;

use LauLaman\ReceiptPrinter\Domain\Enum\QRCodeErrorCorrection;

final readonly class QRCode implements BarcodeCommand
{
    public function __construct(
        public string $data,
        public int $size = 5,
        public QRCodeErrorCorrection $errorCorrection = QRCodeErrorCorrection::MEDIUM,
    ) {}
}