<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Paper;

use LauLaman\ReceiptPrinter\Domain\Enum\PrinterSpeed;

final readonly class PrintSpeed implements PaperCommand
{
    public function __construct(
        public PrinterSpeed $speed = PrinterSpeed::HIGH,
    ) {
    }
}