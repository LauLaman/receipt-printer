<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\PrinterSetting\PrintSetting;

use LauLaman\ReceiptPrinter\Domain\Enum\FontType;

final class CurrentFont implements PrintSetting
{
    public function __construct(
        public FontType $font
    ) {
    }
}