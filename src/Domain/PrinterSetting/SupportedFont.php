<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\PrinterSetting;

use LauLaman\ReceiptPrinter\Domain\Enum\FontType;
use LauLaman\ReceiptPrinter\Domain\PrinterSetting\SupportedFont\CharacterSize;

final readonly class SupportedFont
{
    public function __construct(
        public FontType $font,
        public CharacterSize $characterSize
    ) {
    }
}