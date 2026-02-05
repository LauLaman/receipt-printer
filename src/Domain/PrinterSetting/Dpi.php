<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\PrinterSetting;

final readonly class Dpi
{
    public function __construct(
        public int $value
    ) {
    }
}