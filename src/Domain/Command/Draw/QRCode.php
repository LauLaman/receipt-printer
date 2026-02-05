<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Draw;

use LauLaman\ReceiptPrinter\Domain\Enum\QRCodeErrorCorrection;

final readonly class QRCode implements DrawCommand
{
    public function __construct(
        public string $data,
        public int $size = 5,
        public QRCodeErrorCorrection $errorCorrection = QRCodeErrorCorrection::MEDIUM,
    ) {}
}