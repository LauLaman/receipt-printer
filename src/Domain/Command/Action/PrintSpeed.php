<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Action;

use LauLaman\ReceiptPrinter\Domain\Enum\PrinterSpeed;

final readonly class PrintSpeed implements ActionCommand
{
    public function __construct(
        public PrinterSpeed $speed = PrinterSpeed::HIGH,
    ) {
    }
}